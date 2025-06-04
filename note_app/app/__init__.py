import nltk
try:
    nltk.data.find('tokenizers/punkt')
except LookupError:
    nltk.download('punkt', quiet=True)
try:
    nltk.data.find('corpora/stopwords')
except LookupError:
    nltk.download('stopwords', quiet=True)

from flask import Flask
from config import Config as AppConfig # Rename to avoid conflict
from flask_sqlalchemy import SQLAlchemy
from flask_login import LoginManager
import markdown
import flask_whooshalchemyplus as whooshalchemy

# Initialize extensions without app instances first
db = SQLAlchemy()
login_manager = LoginManager()
# whooshalchemy instance is created and configured inside create_app if needed for testing
# or we can initialize it here if its init_app method handles deferred app binding well.
# For Flask-WhooshAlchemyPlus, init_app is usually sufficient.
# However, to control WHOOSH_BASE for tests, it's better to handle it in create_app.
# For simplicity now, let's assume direct init_app is fine for the main app,
# and tests will override WHOOSH_BASE.
# Alternatively, delay whooshalchemy.init_app(app) to inside create_app.
# Let's try delaying it:
# whoosh = whooshalchemy.WhooshAlchemy() # If it supports this pattern
# For Flask-WhooshAlchemyPlus, it's often just a module:
# import flask_whooshalchemyplus as whooshalchemy # already done

def create_app(config_class=AppConfig, config_overrides=None):
    app = Flask(__name__)
    app.config.from_object(config_class)
    if config_overrides:
        app.config.update(config_overrides)

    db.init_app(app)
    login_manager.init_app(app)
    if app.config.get("WHOOSH_BASE"): # Only init Whoosh if WHOOSH_BASE is set
        whooshalchemy.init_app(app)


    login_manager.login_view = 'main.login' # Updated to use blueprint
    login_manager.login_message_category = 'info'

    from .routes import main_bp # Import the blueprint
    app.register_blueprint(main_bp)

    # Import models here to ensure they are known to SQLAlchemy before db operations
    from . import models

    with app.app_context():
        @login_manager.user_loader
        def load_user(user_id):
            return models.User.query.get(int(user_id))

        # Jinja2 filter for Markdown
        @app.template_filter('md_to_html')
        def md_to_html(md_string):
            return markdown.markdown(md_string)

        # Global context processor for search form
        # Must be defined or imported here to be registered with the app
        from .forms import SearchForm # Import SearchForm
        from flask_login import current_user # Import current_user
        from flask import request # Import request

        @app.context_processor
        def inject_search_form():
            if hasattr(current_user, 'is_authenticated') and current_user.is_authenticated:
                return dict(search_form=SearchForm(request.args))
            return dict(search_form=None)

    return app
