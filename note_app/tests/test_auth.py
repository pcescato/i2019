import pytest
from flask import url_for
from note_app.app.models import User

def test_register_page_loads(test_client, test_app): # Added test_app for context
    with test_app.test_request_context(): # Ensure context for url_for
        response = test_client.get(url_for('main.register'))
    assert response.status_code == 200
    assert b"Register New Account" in response.data

def test_login_page_loads(test_client, test_app): # Added test_app for context
    with test_app.test_request_context():
        response = test_client.get(url_for('main.login'))
    assert response.status_code == 200
    assert b"Login" in response.data

def test_successful_registration(test_client, db_session, test_app): # Changed db to db_session
    with test_app.test_request_context():
        response = test_client.post(url_for('main.register'), data={
            'username': 'newuser',
            'email': 'new@example.com',
        'password': 'password123',
        'confirm_password': 'password123'
    }, follow_redirects=True)
    assert response.status_code == 200
    assert b"My Notes" in response.data
    user = User.query.filter_by(username='newuser').first()
    assert user is not None
    assert user.email == 'new@example.com'
    assert user.check_password('password123')

def test_registration_existing_username(test_client, db_session, logged_in_user, test_app):
    # logged_in_user fixture creates 'testuser_loggedin'
    with test_app.test_request_context():
        response = test_client.post(url_for('main.register'), data={
            'username': 'testuser_loggedin',
            'email': 'another@example.com',
            'password': 'password123',
            'confirm_password': 'password123'
        }, follow_redirects=True)
    assert response.status_code == 200
    assert b"That username is taken" in response.data
    assert b"Register New Account" in response.data

def test_successful_login(test_client, db_session, logged_in_user, test_app):
    # logged_in_user fixture logs in 'testuser_loggedin'
    with test_app.test_request_context():
        response = test_client.get(url_for('main.index'))
    assert response.status_code == 200
    assert b"Welcome, testuser_loggedin" in response.data
    assert b"Logout" in response.data

def test_login_incorrect_password(test_client, db_session, logged_in_user, test_app):
    # User 'testuser_loggedin' exists from logged_in_user fixture
    with test_app.test_request_context():
        test_client.get(url_for('main.logout'), follow_redirects=True) # Logout first

        response = test_client.post(url_for('main.login'), data={
            'username_or_email': 'testuser_loggedin',
            'password': 'wrongpassword'
        }, follow_redirects=True)
    assert response.status_code == 200
    assert b"Invalid username/email or password" in response.data
    assert b"Login" in response.data

def test_logout(test_client, logged_in_user, test_app):
    with test_app.test_request_context():
        response = test_client.get(url_for('main.logout'), follow_redirects=True)
        assert response.status_code == 200
        assert b"You have been logged out." in response.data
        assert b"Login" in response.data

        response_protected = test_client.get(url_for('main.index'), follow_redirects=False)
        assert response_protected.status_code == 302
        assert url_for('main.login') in response_protected.location

def test_access_protected_route_unauthenticated(test_client, test_app):
    with test_app.test_request_context():
        response_index = test_client.get(url_for('main.index'), follow_redirects=False)
        assert response_index.status_code == 302
        assert url_for('main.login') in response_index.location

        response_note = test_client.get(url_for('main.view_note', note_id=1), follow_redirects=False)
        assert response_note.status_code == 302
        assert url_for('main.login') in response_note.location
