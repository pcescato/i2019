# AI-Powered Note-Taking App

## 1. Project Title and Description

**App Name:** AI-Powered Note-Taking App (NoteApp)

**Description:**
NoteApp is a web-based application designed to help users create, manage, and organize their notes efficiently. It leverages modern web technologies and incorporates AI-powered features to enhance the note-taking experience. Key functionalities include Markdown support for rich text editing, flexible tagging for organization, full-text search, user authentication for privacy, and AI-driven features like note summarization, tag suggestion, and finding related notes.

## 2. Features

*   **User Authentication:** Secure user registration, login, and logout system. Users can only access their own notes.
*   **CRUD Operations for Notes:** Create, Read, Update, and Delete notes.
*   **Markdown Support:** Write notes using Markdown for rich text formatting, which are then rendered as HTML.
*   **Tagging System:** Assign multiple tags to notes for better organization and filtering.
*   **Full-Text Search:** Search through your notes by keywords in the title or content.
*   **AI-Powered Features:**
    *   **Note Summarization:** Automatically generate a concise summary of a note.
    *   **Tag Suggestion:** Get AI-driven suggestions for tags based on note content.
    *   **Related Notes:** Discover notes with semantically similar content.
*   **Minimalist Web Interface:** Clean and user-friendly interface.

## 3. Tech Stack

*   **Backend:** Python, Flask
*   **Database:** SQLAlchemy ORM with SQLite (for development and default deployment)
*   **Frontend:** HTML, CSS (Bootstrap with custom enhancements)
*   **Key Libraries:**
    *   `Flask-Login`: User session management.
    *   `Flask-WTF`: Form handling and CSRF protection.
    *   `Werkzeug`: Password hashing, WSGI utilities.
    *   `Flask-SQLAlchemy`: Database interaction.
    *   `Markdown`: For rendering note content.
    *   `Flask-WhooshAlchemyPlus`: Full-text search capabilities (Note: Relies on older Flask/SQLAlchemy versions due to compatibility).
    *   `nltk`: Natural Language Toolkit for text processing (used by AI services).
    *   `numpy`: Numerical operations (dependency for AI services).
    *   `sumy`: For AI-powered text summarization (currently commented out in `requirements.txt` to save space in test/dev environments).
    *   `scikit-learn`: For TF-IDF based tag suggestion (currently commented out).
    *   `sentence-transformers`: For generating embeddings for semantic similarity to find related notes (currently commented out).

## 4. Setup and Installation

**Prerequisites:**
*   Python 3.8+
*   pip (Python package installer)

**Steps:**

1.  **Clone the repository:**
    ```bash
    git clone <repository_url>
    cd <repository_directory>/note_app
    ```
    (Assuming the README.md will be in the `note_app` root, alongside the `app` directory and `run.py`)

2.  **Create and activate a virtual environment (recommended):**
    ```bash
    python -m venv venv
    source venv/bin/activate  # On Windows: venv\Scripts\activate
    ```

3.  **Install dependencies:**
    The `requirements.txt` file is currently pinned to older versions of Flask, SQLAlchemy, and Werkzeug to maintain compatibility with `Flask-WhooshAlchemyPlus`. The heavier AI libraries (`sumy`, `scikit-learn`, `sentence-transformers`) are commented out by default to allow easier setup for core feature testing. To enable them, uncomment them in `requirements.txt` before installing.
    ```bash
    pip install -r requirements.txt
    ```

4.  **NLTK Data:**
    On the first run, the application (specifically the NLTK library) will attempt to download necessary data models (`punkt` for sentence tokenization and `stopwords` for stopword lists) if they are not found in the default NLTK data path. This usually happens automatically.

5.  **AI Models (if libraries are enabled):**
    If you uncomment and install `sentence-transformers`, it will download pre-trained model files (e.g., `all-MiniLM-L6-v2`) on its first use. This can take some time and requires an internet connection.

## 5. Running the Application

1.  **Start the Flask development server:**
    From the `note_app` root directory (where `run.py` is located):
    ```bash
    python run.py
    ```

2.  **Access the application:**
    Open your web browser and go to: `http://127.0.0.1:5000`

## 6. Running Tests

1.  **Set up Python Path:**
    Ensure the application's root directory is in your `PYTHONPATH`. From the directory containing `note_app`:
    ```bash
    export PYTHONPATH=.  # On Linux/macOS
    # set PYTHONPATH=.    # On Windows (cmd)
    # $env:PYTHONPATH="." # On Windows (PowerShell)
    ```
    Alternatively, run pytest from the parent directory of `note_app` using `pytest note_app/tests/`.

2.  **Run tests using pytest:**
    Navigate to the `note_app` directory (containing `run.py`, `app/`, `tests/`) and run:
    ```bash
    pytest tests/
    ```
    Or, from the parent directory:
    ```bash
    pytest note_app/tests/
    ```

3.  **Important Note on Current Test Status:**
    *   Currently, a significant number of unit tests related to database operations within POST requests (e.g., form validations that query the database during user registration, and most note management tests that rely on a logged-in user fixture) are **failing**.
    *   The primary error is `sqlalchemy.exc.OperationalError: (sqlite3.OperationalError) no such table: user`. This issue occurs despite various database configurations for testing (in-memory, file-based with shared cache, direct file-based) and seems to stem from a subtle interaction within the test environment concerning how the Flask-SQLAlchemy session/engine state is managed between the test fixture's `db.create_all()` call and the operations occurring within the application context during a `test_client` request.
    *   Basic GET request tests (page loads), tests for unauthenticated access, and placeholder/skipped AI service tests are generally passing.
    *   **Due to these limitations, thorough manual testing of all features, especially those involving database writes and user-specific data access, is crucial.**

## 7. Manual Testing Checklist

Given the current limitations of the automated test suite, please perform the following manual tests:

*   **User Registration:**
    *   [ ] Successful new user registration.
    *   [ ] Attempt registration with an already existing username.
    *   [ ] Attempt registration with an already existing email.
    *   [ ] Check password confirmation validation (mismatched passwords).
*   **User Login/Logout:**
    *   [ ] Login with correct credentials.
    *   [ ] Attempt login with an incorrect password.
    *   [ ] Attempt login with a non-existent username.
    *   [ ] Logout functionality.
    *   [ ] "Remember Me" functionality (if implemented and testable).
*   **Note Management (CRUD) - *Perform as a logged-in user*:**
    *   [ ] Create a new note with a title, content (using Markdown), and comma-separated tags.
    *   [ ] View the list of personal notes (should only show notes created by the logged-in user).
    *   [ ] View the detail page of a single note (check Markdown rendering, displayed tags).
    *   [ ] Edit an existing note (update title, content, tags - ensure tags are replaced, not appended).
    *   [ ] Delete a note.
    *   [ ] *Security:* Attempt to view/edit/delete a note belonging to another user by manually crafting URLs (should result in a 403 Forbidden error or redirect).
*   **Tagging - *Perform as a logged-in user*:**
    *   [ ] Add multiple tags (comma-separated) to a note during creation and edit.
    *   [ ] Click on a tag displayed on a note; verify it filters to the "Notes tagged with '[tag_name]'" page.
    *   [ ] Ensure the "notes by tag" page only shows notes of the current user with that tag.
*   **Search - *Perform as a logged-in user*:**
    *   [ ] Search for notes using keywords present in titles or content.
    *   [ ] Verify search results are accurate.
    *   [ ] Verify search results are specific to the logged-in user.
    *   [ ] Test search with no results.
*   **AI Features (if libraries enabled) - *Perform as a logged-in user*:**
    *   [ ] Check for AI Summary generation on the note detail page with reasonably sized content.
    *   [ ] Check for Suggested Tags on the note detail page.
    *   [ ] Check for Related Notes on the note detail page (create a few similar and dissimilar notes to test).
*   **General:**
    *   [ ] Test all navigation links.
    *   [ ] Check overall usability and flow.
    *   [ ] Perform a basic responsiveness check by resizing the browser window.

## 8. Project Structure

```
note_app/
├── app/                  # Main application package
│   ├── static/           # Static files (CSS, JS, images)
│   │   └── css/
│   │       └── custom.css
│   ├── templates/        # HTML templates
│   │   ├── base.html
│   │   ├── index.html    # (or notes.html for main note list)
│   │   ├── login.html
│   │   ├── register.html
│   │   ├── note_detail.html
│   │   └── ...
│   ├── __init__.py       # Application factory (create_app)
│   ├── models.py         # SQLAlchemy database models (User, Note, Tag)
│   ├── forms.py          # WTForms definitions
│   ├── routes.py         # Application routes (using Flask Blueprint)
│   └── ai_services.py    # AI feature implementations
├── tests/                # Unit and integration tests
│   ├── conftest.py       # Pytest fixtures
│   ├── test_auth.py
│   ├── test_notes.py
│   └── test_ai_services.py
├── config.py             # Configuration settings (SECRET_KEY, DATABASE_URI)
├── requirements.txt      # Python package dependencies
├── run.py                # Script to run the Flask development server
└── README.md             # This file
```

## 9. Deployment (Basic Guidelines)

*   **WSGI Server:** For production, do not use the Flask development server (`python run.py`). Use a production-ready WSGI server like Gunicorn or Waitress.
    Example with Gunicorn: `gunicorn -w 4 "app:create_app()"`
*   **Configuration:**
    *   `SECRET_KEY`: Should be a long, random string and kept secret. Set via an environment variable.
    *   `SQLALCHEMY_DATABASE_URI`: For production, set via an environment variable.
    *   `DEBUG`: Ensure `DEBUG` is `False` in production.
*   **Database:**
    *   While SQLite is used for development, consider PostgreSQL or MySQL for production deployments requiring higher concurrency or scalability. Update `SQLALCHEMY_DATABASE_URI` accordingly. SQLite can be suitable for single-user self-hosting or small applications.
*   **Environment Variables:** Use a `.env` file (add `.env` to `.gitignore`) for development and set actual environment variables in your production environment.

## 10. Contributing

Contributions are welcome! Please feel free to open an issue to discuss a feature or bug, or submit a pull request with your changes.

## 11. License

This project is licensed under the MIT License. See the `LICENSE` file for details (if one is created).
