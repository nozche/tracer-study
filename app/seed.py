from datetime import datetime, timedelta

from .database import Base, SessionLocal, engine
from .models import Form, Invitation, Question, QuestionType


def seed():
    Base.metadata.create_all(bind=engine)
    db = SessionLocal()

    if db.query(Form).count() > 0:
        db.close()
        return

    form = Form(
        title="Tracer Study Alumni 2024",
        active_from=datetime.utcnow() - timedelta(days=1),
        active_until=datetime.utcnow() + timedelta(days=7),
        allow_edit_after_submit=True,
    )
    db.add(form)
    db.flush()

    questions = [
        Question(form_id=form.id, prompt="Nama lengkap", type=QuestionType.short_text, required=True),
        Question(form_id=form.id, prompt="Program studi", type=QuestionType.short_text, required=True),
        Question(
            form_id=form.id,
            prompt="Status pekerjaan saat ini",
            type=QuestionType.single_choice,
            required=True,
            options=["Bekerja", "Berwirausaha", "Melanjutkan studi", "Lainnya"],
        ),
        Question(
            form_id=form.id,
            prompt="Kesan terhadap kurikulum",
            type=QuestionType.long_text,
            required=False,
        ),
    ]
    db.add_all(questions)
    db.flush()

    invitation = Invitation(alumni_name="Rina Sembiring", token="sample-token-123", form_id=form.id)
    db.add(invitation)
    db.commit()
    db.close()


if __name__ == "__main__":
    seed()
