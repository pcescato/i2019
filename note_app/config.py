import os

class Config:
    SECRET_KEY = os.environ.get('SECRET_KEY') or 'you-will-never-guess'
    SQLALCHEMY_DATABASE_URI = os.environ.get('DATABASE_URI') or 'sqlite:///notes.db'
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    WHOOSH_BASE = os.path.join(os.path.abspath(os.path.dirname(__file__)), 'whoosh_index')
