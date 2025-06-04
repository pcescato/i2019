import pytest
from flask import url_for
from note_app.app.models import Note, Tag, User

def test_create_note_page_loads(test_client, logged_in_user, test_app):
    with test_app.test_request_context():
        response = test_client.get(url_for('main.index')) # Create form is on index page
    assert response.status_code == 200
    assert b"Create Note" in response.data

def test_create_note_successfully(test_client, db_session, logged_in_user, test_app):
    with test_app.test_request_context():
        response = test_client.post(url_for('main.index'), data={
            'title': 'My Test Note',
            'content': 'This is the content of my test note. #test #pytest',
            'tags': 'test, pytest, important'
        }, follow_redirects=True)
    assert response.status_code == 200
    assert b"Your note has been created!" in response.data
    assert b"My Test Note" in response.data

    note = Note.query.filter_by(title='My Test Note').first()
    assert note is not None
    assert note.author == logged_in_user
    assert note.content == 'This is the content of my test note. #test #pytest'

    tag_names = {tag.name for tag in note.tags}
    assert 'test' in tag_names
    assert 'pytest' in tag_names
    assert 'important' in tag_names
    assert len(tag_names) == 3

def test_view_note_detail(test_client, db_session, logged_in_user, test_app):
    # Create a note first
    note = Note(title="Detail Test Note", content="Content for detail view.", author=logged_in_user)
    db_session.session.add(note)
    db_session.session.commit()

    with test_app.test_request_context():
        response = test_client.get(url_for('main.view_note', note_id=note.id))
    assert response.status_code == 200
    assert b"Detail Test Note" in response.data
    assert b"Content for detail view." in response.data
    assert b"AI Summary" in response.data
    assert b"Suggested Tags" in response.data
    assert b"Related Notes" in response.data


def test_edit_note(test_client, db_session, logged_in_user, test_app):
    note = Note(title="Editable Note", content="Initial content.", author=logged_in_user)
    tag1 = Tag(name="original_tag")
    note.tags.append(tag1)
    db_session.session.add_all([note, tag1])
    db_session.session.commit()

    with test_app.test_request_context():
        # GET edit page
        response_get = test_client.get(url_for('main.edit_note', note_id=note.id))
        assert response_get.status_code == 200
        assert b"Editable Note" in response_get.data
        assert b"Initial content." in response_get.data
        assert b"original_tag" in response_get.data

        # POST updated data
        response_post = test_client.post(url_for('main.edit_note', note_id=note.id), data={
            'title': 'Updated Note Title',
            'content': 'Updated content here.',
            'tags': 'updated_tag, new_tag'
        }, follow_redirects=True)
    assert response_post.status_code == 200
    assert b"Your note has been updated!" in response_post.data
    assert b"Updated Note Title" in response_post.data

    updated_note = Note.query.get(note.id)
    assert updated_note.title == 'Updated Note Title'
    assert updated_note.content == 'Updated content here.'
    tag_names = {tag.name for tag in updated_note.tags}
    assert "updated_tag" in tag_names
    assert "new_tag" in tag_names
    assert "original_tag" not in tag_names

def test_delete_note(test_client, db_session, logged_in_user, test_app):
    note = Note(title="Deletable Note", content="Content to delete.", author=logged_in_user)
    db_session.session.add(note)
    db_session.session.commit()
    note_id = note.id

    with test_app.test_request_context():
        response = test_client.post(url_for('main.delete_note', note_id=note_id), follow_redirects=True)
    assert response.status_code == 200
    assert b"Your note has been deleted!" in response.data
    assert Note.query.get(note_id) is None

def test_access_others_note_forbidden(test_client, db_session, logged_in_user, test_app):
    # Create another user and their note
    other_user = User(username="otheruser", email="other@example.com")
    other_user.set_password("password")
    other_note = Note(title="Other's Note", content="Secret content", author=other_user)
    db_session.session.add_all([other_user, other_note])
    db_session.session.commit()

    with test_app.test_request_context():
        # Current logged_in_user ('testuser_loggedin') tries to access other_note
        response_view = test_client.get(url_for('main.view_note', note_id=other_note.id))
        assert response_view.status_code == 403

        response_edit_get = test_client.get(url_for('main.edit_note', note_id=other_note.id))
        assert response_edit_get.status_code == 403

        response_edit_post = test_client.post(url_for('main.edit_note', note_id=other_note.id), data={
            'title': 'Attempted Hack', 'content': '...', 'tags': ''
        })
        assert response_edit_post.status_code == 403

        response_delete = test_client.post(url_for('main.delete_note', note_id=other_note.id))
        assert response_delete.status_code == 403
    assert Note.query.get(other_note.id) is not None

def test_search_user_notes(test_client, db_session, logged_in_user, test_app):
    note1 = Note(title="Searchable Alpha", content="Unique keyword_one for testuser_loggedin", author=logged_in_user)
    note2 = Note(title="Searchable Beta", content="Common content for testuser_loggedin", author=logged_in_user)
    db_session.session.add_all([note1, note2])

    other_user = User(username="anothersearcher", email="asearcher@example.com")
    other_user.set_password("newpass")
    note_other_user = Note(title="Other Searchable Alpha", content="Unique keyword_one for anothersearcher", author=other_user)
    db_session.session.add_all([other_user, note_other_user])
    db_session.session.commit()

    with test_app.test_request_context():
        response = test_client.get(url_for('main.search', query="keyword_one"))
    assert response.status_code == 200
    assert b"Searchable Alpha" in response.data
    assert b"Other Searchable Alpha" not in response.data

    with test_app.test_request_context():
        response_common = test_client.get(url_for('main.search', query="Common content"))
    assert response_common.status_code == 200
    assert b"Searchable Beta" in response_common.data

    with test_app.test_request_context():
        response_no_match = test_client.get(url_for('main.search', query="nonexistentterm"))
    assert response_no_match.status_code == 200
    assert b"No results found for \"nonexistentterm\"" in response_no_match.data
