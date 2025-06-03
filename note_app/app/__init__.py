import nltk
try:
    nltk.data.find('tokenizers/punkt')
except nltk.downloader.DownloadError:
    nltk.download('punkt', quiet=True)
try:
    nltk.data.find('corpora/stopwords')
except nltk.downloader.DownloadError:
    nltk.download('stopwords', quiet=True)

from flask import Flask
from config import Config
from flask_sqlalchemy import SQLAlchemy
from flask_login import LoginManager
import markdown
import flask_whooshalchemyplus as whooshalchemy

app = Flask(__name__)
app.config.from_object(Config)
db = SQLAlchemy(app)
whooshalchemy.init_app(app)
login_manager = LoginManager(app)
login_manager.login_view = 'login' # Name of the login route function
login_manager.login_message_category = 'info' # Bootstrap class for flash messages

# Jinja2 filter for Markdown
@app.template_filter('md_to_html')
def md_to_html(md_string):
    return markdown.markdown(md_string)

from app import routes, models # models needs to be imported before this for user_loader

@login_manager.user_loader
def load_user(user_id):
    return models.User.query.get(int(user_id))
