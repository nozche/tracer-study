import os
from datetime import datetime, timedelta

import pytest
from fastapi.testclient import TestClient

os.environ["DATABASE_URL"] = "sqlite:///./test_tracer.db"

from app.database import Base, SessionLocal, engine  # noqa: E402
from app.main import app  # noqa: E402
from app.models import Form, Invitation, Question, QuestionType  # noqa: E402


client = TestClient(app)


@pytest.fixture(autouse=True)
def setup_db():
    Base.metadata.drop_all(bind=engine)
    Base.metadata.create_all(bind=engine)
    db = SessionLocal()
    form = Form(
        title="Tracer Study Test",
        active_from=datetime.utcnow() - timedelta(days=1),
        active_until=datetime.utcnow() + timedelta(days=1),
        allow_edit_after_submit=False,
    )
    db.add(form)
    db.flush()
    questions = [
        Question(form_id=form.id, prompt="Q1", type=QuestionType.short_text, required=True),
        Question(form_id=form.id, prompt="Q2", type=QuestionType.long_text, required=False),
    ]
    db.add_all(questions)
    invitation = Invitation(alumni_name="Tester", token="token-1", form_id=form.id)
    db.add(invitation)
    db.commit()
    question_ids = [q.id for q in questions]
    yield {"question_ids": question_ids}
    db.close()
    Base.metadata.drop_all(bind=engine)


def test_autosave_draft(setup_db):
    first_question = setup_db["question_ids"][0]
    payload = {"answers": [{"question_id": first_question, "value": "John"}], "submit": False}
    response = client.post("/forms/token-1/responses", json=payload)
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "draft"
    assert data["answers"][0]["value"] == "John"


def test_submit_requires_required_fields(setup_db):
    second_question = setup_db["question_ids"][1]
    payload = {"answers": [{"question_id": second_question, "value": "Optional"}], "submit": True}
    response = client.post("/forms/token-1/responses", json=payload)
    assert response.status_code == 422


def test_submit_success_and_locking(setup_db):
    first_question, second_question = setup_db["question_ids"]
    payload = {
        "answers": [
            {"question_id": first_question, "value": "John"},
            {"question_id": second_question, "value": "Optional"},
        ],
        "submit": True,
    }
    response = client.post("/forms/token-1/responses", json=payload)
    assert response.status_code == 200
    assert response.json()["status"] == "submitted"

    update_attempt = client.post("/forms/token-1/responses", json=payload)
    assert update_attempt.status_code == 403


def test_reopen_allows_edit_when_permitted(setup_db):
    first_question = setup_db["question_ids"][0]
    db = SessionLocal()
    form = db.query(Form).first()
    form.allow_edit_after_submit = True
    db.commit()
    db.close()

    payload = {"answers": [{"question_id": first_question, "value": "John"}], "submit": True}
    client.post("/forms/token-1/responses", json=payload)

    reopen = client.post("/forms/token-1/reopen")
    assert reopen.status_code == 200
    assert reopen.json()["status"] == "draft"
