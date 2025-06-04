from flask import Blueprint, render_template, redirect, url_for, flash, request, abort, current_app
from . import db # Use relative import for db from current package (__init__.py)
from .models import User, Note, Tag
from .forms import NoteForm, SearchForm, RegistrationForm, LoginForm
from .ai_services import summarize_text, suggest_tags, get_related_notes, similarity_model
from flask_login import current_user, login_user, logout_user, login_required

main_bp = Blueprint('main', __name__) # Create a Blueprint

# Authentication Routes
@main_bp.route('/register', methods=['GET', 'POST'])
def register():
    if current_user.is_authenticated:
        return redirect(url_for('main.index'))
    form = RegistrationForm()
    if form.validate_on_submit():
        user = User(username=form.username.data, email=form.email.data)
        user.set_password(form.password.data)
        db.session.add(user)
        db.session.commit()
        flash('Congratulations, you are now a registered user!', 'success')
        login_user(user)
        return redirect(url_for('main.index'))
    return render_template('register.html', title='Register', form=form)

@main_bp.route('/login', methods=['GET', 'POST'])
def login():
    if current_user.is_authenticated:
        return redirect(url_for('main.index'))
    form = LoginForm()
    if form.validate_on_submit():
        user = User.query.filter((User.username == form.username_or_email.data) | (User.email == form.username_or_email.data)).first()
        if user is None or not user.check_password(form.password.data):
            flash('Invalid username/email or password', 'danger')
            return redirect(url_for('login'))
        login_user(user, remember=form.remember_me.data)
        next_page = request.args.get('next')
        if not next_page or not next_page.startswith('/'): # Security: ensure next_page is local
            next_page = url_for('main.index')
        flash('Logged in successfully.', 'success')
        return redirect(next_page)
    return render_template('login.html', title='Login', form=form)

@main_bp.route('/logout')
@login_required
def logout():
    logout_user()
    flash('You have been logged out.', 'info')
    return redirect(url_for('main.login'))

# Note Management Routes
@main_bp.route('/', methods=['GET', 'POST'])
@main_bp.route('/notes', methods=['GET', 'POST'])
@login_required
def index():
    form = NoteForm()
    if form.validate_on_submit():
        note = Note(title=form.title.data, content=form.content.data, author=current_user)
        tag_names = [name.strip() for name in form.tags.data.split(',') if name.strip()]
        for tag_name in tag_names:
            tag = Tag.query.filter_by(name=tag_name).first()
            if not tag:
                tag = Tag(name=tag_name)
                # Consider adding tag to session here, but commit with note
                db.session.add(tag)
            note.tags.append(tag)
        db.session.add(note)
        db.session.commit()
        flash('Your note has been created!', 'success')
        return redirect(url_for('main.index'))

    notes = Note.query.filter_by(author=current_user).order_by(Note.created_at.desc()).all()
    return render_template('notes.html', title='My Notes', form=form, notes=notes)

@main_bp.route('/note/<int:note_id>')
@login_required
def view_note(note_id):
    note = Note.query.get_or_404(note_id)
    if note.author != current_user:
        abort(403) # Forbidden
    summary = summarize_text(note.content)
    suggested_tags_list = suggest_tags(note.content)

    all_other_user_notes = Note.query.filter(Note.user_id == current_user.id, Note.id != note_id).all()
    related_notes_list = get_related_notes(note, all_other_user_notes, similarity_model)

    return render_template('note_detail.html',
                           title=note.title,
                           note=note,
                           summary=summary,
                           suggested_tags=suggested_tags_list,
                           related_notes=related_notes_list)

@main_bp.route('/note/<int:note_id>/edit', methods=['GET', 'POST'])
@login_required
def edit_note(note_id):
    note = Note.query.get_or_404(note_id)
    if note.author != current_user:
        abort(403)
    form = NoteForm()
    if form.validate_on_submit():
        note.title = form.title.data
        note.content = form.content.data
        note.tags.clear()
        tag_names = [name.strip() for name in form.tags.data.split(',') if name.strip()]
        for tag_name in tag_names:
            tag = Tag.query.filter_by(name=tag_name).first()
            if not tag:
                tag = Tag(name=tag_name)
                db.session.add(tag)
            note.tags.append(tag)
        # note.updated_at is handled by onupdate=datetime.utcnow
        db.session.commit()
        flash('Your note has been updated!', 'success')
        return redirect(url_for('main.view_note', note_id=note.id))
    elif request.method == 'GET':
        form.title.data = note.title
        form.content.data = note.content
        form.tags.data = ', '.join([tag.name for tag in note.tags])
    return render_template('edit_note.html', title='Edit Note', form=form, note=note)

@main_bp.route('/note/<int:note_id>/delete', methods=['POST'])
@login_required
def delete_note(note_id):
    note = Note.query.get_or_404(note_id)
    if note.author != current_user:
        abort(403)
    db.session.delete(note)
    db.session.commit()
    flash('Your note has been deleted!', 'success')
    return redirect(url_for('main.index'))

# Tag and Search routes need to be user-aware
@main_bp.route('/tag/<string:tag_name>')
@login_required
def notes_by_tag(tag_name):
    tag = Tag.query.filter_by(name=tag_name).first_or_404()
    # Filter notes by current user and the specific tag
    notes = Note.query.join(Note.tags).filter(Tag.id == tag.id, Note.user_id == current_user.id).order_by(Note.created_at.desc()).all()
    return render_template('notes_by_tag.html', notes=notes, tag_name=tag.name, title=f"Notes tagged with {tag.name}")

@main_bp.route('/search', methods=['GET'])
@login_required
def search():
    query = request.args.get('query', '') # Get query from SearchForm submission in base.html
    search_form_instance = SearchForm(request.args) # For validation if needed, or just to pass to template

    if not query.strip(): # If query is empty or just whitespace
        notes = []
    else:
        notes_query = Note.query.whoosh_search(query, or_=True).filter(Note.user_id == current_user.id)
        notes = notes_query.all()

    return render_template('search_results.html', title='Search Results', notes=notes, query=query, search_form=search_form_instance)

# The context processor 'inject_search_form' has been moved to app/__init__.py
# and is registered with the app instance there.
# This file should primarily define blueprint routes.
