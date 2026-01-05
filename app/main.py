from datetime import datetime
from typing import List

from fastapi import Depends, FastAPI, HTTPException, status
from sqlalchemy.orm import Session

from . import models, schemas
from .database import Base, engine, get_db


Base.metadata.create_all(bind=engine)

app = FastAPI(title="Tracer Study Survey")


def get_active_invitation(token: str, db: Session) -> models.Invitation:
    invitation = db.query(models.Invitation).filter(models.Invitation.token == token).first()
    if not invitation:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Invitation not found")

    form = invitation.form
    now = datetime.utcnow()
    if form.active_from and now < form.active_from:
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Form is not yet active")
    if form.active_until and now > form.active_until:
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Form is no longer active")

    return invitation


def find_existing_response(invitation: models.Invitation, db: Session) -> models.Response | None:
    return (
        db.query(models.Response)
        .filter(models.Response.invitation_id == invitation.id, models.Response.form_id == invitation.form_id)
        .first()
    )


def persist_answers(response: models.Response, answers: List[schemas.AnswerPayload], db: Session) -> None:
    existing_answers = {answer.question_id: answer for answer in response.answers}
    for incoming in answers:
        if incoming.question_id in existing_answers:
            existing_answers[incoming.question_id].value = incoming.value
        else:
            db.add(
                models.Answer(
                    response=response,
                    question_id=incoming.question_id,
                    value=incoming.value,
                )
            )


def validate_required(form: models.Form, answers: List[schemas.AnswerPayload]):
    answer_lookup = {a.question_id: a.value for a in answers}
    missing_required = [
        question.prompt
        for question in form.questions
        if question.required and (question.id not in answer_lookup or not str(answer_lookup[question.id]).strip())
    ]
    if missing_required:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail=f"Required questions missing answers: {', '.join(missing_required)}",
        )


def validate_questions_belong_to_form(form: models.Form, answers: List[schemas.AnswerPayload]):
    question_ids = {q.id for q in form.questions}
    invalid = [str(answer.question_id) for answer in answers if answer.question_id not in question_ids]
    if invalid:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Questions not part of this form: {', '.join(invalid)}",
        )


@app.get("/forms/{token}", response_model=schemas.FormWithInvitationSchema)
def get_form(token: str, db: Session = Depends(get_db)):
    invitation = get_active_invitation(token, db)
    response = find_existing_response(invitation, db)
    return {
        "form": invitation.form,
        "invitation_token": invitation.token,
        "invitation_id": invitation.id,
        "existing_response": response,
    }


@app.post("/forms/{token}/responses", response_model=schemas.ResponseSchema)
def save_response(token: str, payload: schemas.ResponsePayload, db: Session = Depends(get_db)):
    invitation = get_active_invitation(token, db)
    form = invitation.form

    response = find_existing_response(invitation, db)
    if not response:
        response = models.Response(
            form_id=form.id,
            invitation_id=invitation.id,
            status=models.ResponseStatus.draft,
        )
        db.add(response)
        db.flush()

    if response.status == models.ResponseStatus.submitted and not form.allow_edit_after_submit:
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Submission is locked for this form")

    validate_questions_belong_to_form(form, payload.answers)
    persist_answers(response, payload.answers, db)

    if payload.submit:
        validate_required(form, payload.answers)
        response.status = models.ResponseStatus.submitted
    else:
        response.status = models.ResponseStatus.draft

    db.commit()
    db.refresh(response)
    return response


@app.post("/forms/{token}/reopen", response_model=schemas.ResponseSchema)
def reopen_response(token: str, db: Session = Depends(get_db)):
    invitation = get_active_invitation(token, db)
    response = find_existing_response(invitation, db)
    if not response:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="No response exists to reopen")

    form = invitation.form
    if response.status != models.ResponseStatus.submitted:
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="Response is not submitted")

    if not form.allow_edit_after_submit:
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="This form does not allow edits")

    response.status = models.ResponseStatus.draft
    db.commit()
    db.refresh(response)
    return response
