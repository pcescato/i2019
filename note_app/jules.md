feat: Initial version of AI-powered note-taking app

This commit includes the initial development of a Flask-based note-taking application with a comprehensive set of features.

Key features implemented:
1.  **Project Setup:** Basic Flask application structure with directories for app, static files, and templates. Configuration for database and Whoosh search.
2.  **Database Schema:** SQLite database with `Note` and `Tag` models, including a many-to-many relationship. Later, a `User` model was added for authentication.
3.  **Core Note Management (CRUD):**
    *   Creation, viewing (list and detail), updating, and deletion of notes.
    *   Markdown support for note content, rendered to HTML.
4.  **Tagging System:**
    *   Ability to add multiple tags to notes.
    *   Filtering notes by specific tags.
    *   Clickable tags for easy navigation.
5.  **Full-Text Search:**
    *   Integrated WhooshAlchemyPlus for searching note titles and content.
    *   Global search bar available.
6.  **Web Interface:**
    *   Minimalist and clean UI using Bootstrap.
    *   Custom CSS for improved aesthetics and readability.
    *   Responsive design for basic usability on different screen sizes.
7.  **AI-Powered Features:**
    *   **Phase 1 (Basic):**
        *   Note summarization using `sumy`.
        *   Tag suggestion based on content using TF-IDF from `scikit-learn`.
        *   NLTK for text processing (tokenization, stopwords).
    *   **Phase 2 (Advanced):**
        *   Finding related notes using semantic similarity with `sentence-transformers` (`all-MiniLM-L6-v2` model).
8.  **User Authentication:**
    *   Implemented using `Flask-Login`.
    *   User registration, login, and logout.
    *   Notes are associated with users, ensuring privacy (you can only access your own notes).
    *   Protected routes and database queries filtered by the current user.

The application structure is modular, with forms, models, routes, AI services, and templates organized within the `note_app` directory.

I was about to begin comprehensive testing (unit and manual) and further refinement.
