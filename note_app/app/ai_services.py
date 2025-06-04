# AI Services
# Conditionally import heavy libraries to allow core tests to run if they are not installed.

try:
    from sentence_transformers import SentenceTransformer, util
    similarity_model = SentenceTransformer('all-MiniLM-L6-v2')
except ImportError:
    SentenceTransformer = None
    util = None
    similarity_model = None
    print("Warning: sentence-transformers not installed. Related notes functionality will be disabled.")
except Exception as e: # Catch other errors during model loading (e.g., network issues)
    SentenceTransformer = None
    util = None
    similarity_model = None
    print(f"Error loading SentenceTransformer model: {e}. Related notes functionality will be disabled.")

try:
    from sumy.parsers.plaintext import PlaintextParser
    from sumy.nlp.tokenizers import Tokenizer
    from sumy.summarizers.lex_rank import LexRankSummarizer
except ImportError:
    PlaintextParser = None
    Tokenizer = None
    LexRankSummarizer = None
    print("Warning: sumy not installed. Summarization functionality will be disabled.")

try:
    from sklearn.feature_extraction.text import TfidfVectorizer
except ImportError:
    TfidfVectorizer = None
    print("Warning: scikit-learn not installed. Tag suggestion functionality will be disabled.")

import nltk
import numpy as np


# Ensure stopwords are downloaded (this might be redundant if already in __init__.py but good for module independence)
try:
    nltk.data.find('corpora/stopwords')
except LookupError: # Changed from nltk.downloader.DownloadError
    nltk.download('stopwords', quiet=True)

def summarize_text(text, sentences_count=3):
    if not PlaintextParser or not Tokenizer or not LexRankSummarizer:
        print("Summarization skipped: sumy library not available.")
        return "Summarization service unavailable."
    if not text or not text.strip():
        return ""
    parser = PlaintextParser.from_string(text, Tokenizer("english"))
    summarizer = LexRankSummarizer()
    summary = summarizer(parser.document, sentences_count)
    return " ".join([str(sentence) for sentence in summary])

def suggest_tags(text, top_n=5):
    if not TfidfVectorizer:
        print("Tag suggestion skipped: scikit-learn library not available.")
        return ["Tag suggestion service unavailable."]
    if not text or not text.strip():
        return []
    try:
        stop_words = nltk.corpus.stopwords.words('english')
    except LookupError:
        nltk.download('stopwords', quiet=True)
        stop_words = nltk.corpus.stopwords.words('english')

    vectorizer = TfidfVectorizer(stop_words=stop_words, max_features=top_n, ngram_range=(1, 2), token_pattern=r'\b[a-zA-Z]{3,}\b')

    try:
        vectorizer.fit_transform([text.lower()])
        feature_names = vectorizer.get_feature_names_out()
        return feature_names.tolist() if feature_names.any() else []
    except ValueError:
        return []

def get_related_notes(current_note, all_other_notes, model, top_n=3):
    if not model or not util: # Check for util as well, as it's imported with SentenceTransformer
        print("Related notes skipped: sentence-transformers library not available or model not loaded.")
        return []
    if not current_note.content or not all_other_notes:
        return []

    # Filter out notes without content from all_other_notes first
    all_other_notes_with_content = [note for note in all_other_notes if note.content and note.content.strip()]
    if not all_other_notes_with_content:
        return []

    try:
        current_embedding = model.encode(current_note.content, convert_to_tensor=False, show_progress_bar=False)

        other_contents = [note.content for note in all_other_notes_with_content]
        other_embeddings = model.encode(other_contents, convert_to_tensor=False, show_progress_bar=False)

        # Compute cosine similarity
        cosine_scores = util.cos_sim(current_embedding, other_embeddings)

        similarities = []
        for i, note in enumerate(all_other_notes_with_content):
            # Exclude the current note itself if it happens to be in all_other_notes
            if note.id == current_note.id:
                continue
            similarities.append({'note': note, 'score': cosine_scores[0][i].item()})

        # Sort by similarity score
        similarities.sort(key=lambda x: x['score'], reverse=True)

        # Return top_n note objects
        return [item['note'] for item in similarities[:top_n]]

    except Exception as e:
        print(f"Error getting related notes: {e}")
        return []
