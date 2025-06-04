from app import create_app, db

app = create_app()

# Create database tables if they don't exist
# This should be handled carefully. For development, it's okay.
# For production, migrations (e.g. Flask-Migrate) are better.
with app.app_context():
    db.create_all()

if __name__ == '__main__':
    app.run(debug=True)
