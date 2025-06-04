import pytest
import os # Import os module
from flask import url_for
from note_app.app import create_app
from note_app.app import db as _db
from note_app.app.models import User, Note, Tag

# Define path for the test database file within the tests directory
TEST_DB_FILENAME = 'test_notes_app.db'
# Assuming conftest.py is in note_app/tests/
# __file__ is /app/note_app/tests/conftest.py
# os.path.dirname(__file__) is /app/note_app/tests
TEST_DB_PATH = os.path.join(os.path.dirname(__file__), TEST_DB_FILENAME)
TEST_SQLALCHEMY_DATABASE_URI = f'sqlite:///{TEST_DB_PATH}'

@pytest.fixture(scope='function')
def test_app():
    # Ensure the database file is removed before creating the app and after dropping tables
    if os.path.exists(TEST_DB_PATH):
        os.remove(TEST_DB_PATH)
    journal_file = TEST_DB_PATH + "-journal" # SQLite journal file
    if os.path.exists(journal_file):
        os.remove(journal_file)

    app = create_app(config_overrides={
        "TESTING": True,
        "SQLALCHEMY_DATABASE_URI": TEST_SQLALCHEMY_DATABASE_URI, # Use file-based DB
        "WTF_CSRF_ENABLED": False,
        "WHOOSH_BASE": None,
        "LOGIN_DISABLED": False,
        "SECRET_KEY": "test-secret-key",
        "DEBUG": False,
        "SERVER_NAME": "localhost.test"
    })

    with app.app_context():
        _db.create_all()
        yield app
        _db.session.remove()
        _db.drop_all()

    # Final cleanup of the database file and its journal after tests
    if os.path.exists(TEST_DB_PATH):
        os.remove(TEST_DB_PATH)
    if os.path.exists(journal_file):
        os.remove(journal_file)

@pytest.fixture(scope='function')
def test_client(test_app):
    return test_app.test_client()

@pytest.fixture(scope='function')
def db_session(test_app):
    with test_app.app_context():
        yield _db

@pytest.fixture(scope='function')
def logged_in_user(test_client, test_app, db_session):
    REG_USERNAME = 'fixture_user_filedb'
    REG_EMAIL = f'{REG_USERNAME}@example.com'
    REG_PASSWORD = 'password123'

    with test_app.test_request_context():
        register_url = url_for('main.register', _external=False)
        login_url = url_for('main.login', _external=False)

    register_response = test_client.post(
        register_url,
        data={
            'username': REG_USERNAME,
            'email': REG_EMAIL,
            'password': REG_PASSWORD,
            'confirm_password': REG_PASSWORD
        },
        follow_redirects=True
    )
    assert register_response.status_code == 200, \
        f"Registration POST failed in fixture. Status: {register_response.status_code}. Response: {register_response.data.decode()}"

    login_response = test_client.post(
        login_url,
        data={'username_or_email': REG_USERNAME, 'password': REG_PASSWORD},
        follow_redirects=True
    )
    assert login_response.status_code == 200, \
        f"Login POST failed in fixture. Status: {login_response.status_code}. Response: {login_response.data.decode()}"

    with test_app.app_context():
        user = User.query.filter_by(username=REG_USERNAME).first()
        assert user is not None, "User object not found in DB after registration/login in fixture"
        return user
