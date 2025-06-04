import pytest
from note_app.app.ai_services import summarize_text, suggest_tags, get_related_notes, similarity_model
from note_app.app.models import Note # For creating dummy notes

# Sample texts for testing
SAMPLE_TEXT_LONG = """
The quick brown fox jumps over the lazy dog. This sentence is well-known for containing all letters of the English alphabet.
It is often used for practicing typing and displaying fonts. The origin of the sentence is not entirely clear.
Several theories exist, but none are definitively proven. It remains a popular choice for a variety of applications.
The dog, being lazy, did not react much to the fox's antics. Foxes are known for their cunning and agility.
This particular fox was no exception, executing a perfect jump.
"""
SAMPLE_TEXT_SHORT = "This is a short sentence."
EMPTY_TEXT = ""

def test_summarize_text_basic():
    summary = summarize_text(SAMPLE_TEXT_LONG, sentences_count=2)
    assert isinstance(summary, str)
    assert len(summary) > 0
    assert len(summary) < len(SAMPLE_TEXT_LONG)
    # Check if it returns roughly the number of sentences requested.
    # This is approximate as sentence splitting can be complex.
    assert summary.count('.') < 4 # Rough check for ~2 sentences

def test_summarize_text_short_input():
    summary = summarize_text(SAMPLE_TEXT_SHORT, sentences_count=2)
    assert isinstance(summary, str)
    if PlaintextParser: # Check if sumy was imported
        assert summary == SAMPLE_TEXT_SHORT or len(summary) <= len(SAMPLE_TEXT_SHORT)
    else:
        assert summary == "Summarization service unavailable."

def test_summarize_text_empty_input():
    summary = summarize_text(EMPTY_TEXT)
    assert isinstance(summary, str)
    if PlaintextParser: # Check if sumy was imported
        assert summary == ""
    else:
        assert summary == "Summarization service unavailable." # Corrected from "" for the case where sumy is not available

def test_suggest_tags_basic():
    tags = suggest_tags(SAMPLE_TEXT_LONG, top_n=5)
    assert isinstance(tags, list)
    if TfidfVectorizer: # Check if sklearn was imported
        assert len(tags) <= 5
        if tags:
            for tag in tags:
                assert isinstance(tag, str)
                assert len(tag.strip()) > 0
    else:
        assert tags == ["Tag suggestion service unavailable."]


def test_suggest_tags_short_input():
    tags = suggest_tags(SAMPLE_TEXT_SHORT, top_n=3)
    assert isinstance(tags, list)
    if TfidfVectorizer:
        assert len(tags) <= 3
    else:
        assert tags == ["Tag suggestion service unavailable."]


def test_suggest_tags_empty_input():
    tags = suggest_tags(EMPTY_TEXT)
    assert isinstance(tags, list)
    if TfidfVectorizer:
        assert len(tags) == 0
    else:
        assert tags == ["Tag suggestion service unavailable."] # Corrected from len(tags) == 0

# Mocking SentenceTransformer model for get_related_notes can be complex.
# For now, we'll test with the actual model if it loads,
# but this makes the test heavier and dependent on model download.
# A true unit test would mock model.encode and util.cos_sim.

class MockNote:
    def __init__(self, id, content, user_id=1, title=None): # Add title as optional
        self.id = id
        self.title = title if title else f"Note {id}"
        self.content = content
        self.user_id = user_id

    def __repr__(self):
        return f"<MockNote id={self.id}>"

# Import the placeholder variables to check against them
from note_app.app.ai_services import PlaintextParser, TfidfVectorizer

@pytest.mark.skipif(similarity_model is None, reason="SentenceTransformer model failed to load or not installed")
def test_get_related_notes_basic():
    current_note = MockNote(id=1, content="Python is a versatile programming language.")
    other_notes = [
        MockNote(id=2, content="Flask is a micro web framework for Python."), # Similar
        MockNote(id=3, content="Apples are a type of fruit."), # Different
        MockNote(id=4, content="Learning Python opens up many career opportunities."), # Similar
        MockNote(id=5, content="The weather is nice today."), # Different
        MockNote(id=6, content=current_note.content) # Identical content, different id
    ]

    related = get_related_notes(current_note, other_notes, similarity_model, top_n=2)

    assert isinstance(related, list)
    assert len(related) <= 2

    if len(related) > 0:
        related_ids = {note.id for note in related}
        assert 1 not in related_ids # Should not include current_note itself
        # Expected similar notes (ids 2 and 4)
        assert 2 in related_ids or 4 in related_ids or 6 in related_ids # Note 6 has identical content
        if len(related) == 2:
             # Check that the two most similar (excluding self) are returned.
             # This depends on the model's behavior.
             # We expect 6 (identical content) and (2 or 4) to be most similar.
            assert (6 in related_ids and (2 in related_ids or 4 in related_ids))

    # Test case where current_note.content is empty
    current_note_empty_content = MockNote(id=7, content="")
    related_empty = get_related_notes(current_note_empty_content, other_notes, similarity_model, top_n=2)
    assert related_empty == []

    # Test case where all_other_notes is empty
    related_no_others = get_related_notes(current_note, [], similarity_model, top_n=2)
    assert related_no_others == []

@pytest.mark.skipif(similarity_model is None, reason="SentenceTransformer model failed to load")
def test_get_related_notes_excludes_self_if_passed_in_others():
    # Test the specific case where current_note might accidentally be in all_other_notes
    current_note = MockNote(id=1, content="Python is great.")
    all_notes_including_self = [
        current_note, # Current note itself
        MockNote(id=2, content="Python and Flask are great too."),
        MockNote(id=3, content="Java is another language.")
    ]
    related = get_related_notes(current_note, all_notes_including_self, similarity_model, top_n=1)
    assert len(related) <= 1
    if related:
        assert related[0].id == 2 # Should pick note 2, not note 1

    # Test with only current_note in the list of others
    related_only_self = get_related_notes(current_note, [current_note], similarity_model, top_n=1)
    assert related_only_self == []
