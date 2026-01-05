from fastapi import Depends, FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
from sqlalchemy.orm import Session
from sqlalchemy import func

from . import models, schemas
from .database import SessionLocal, engine

models.Base.metadata.create_all(bind=engine)

app = FastAPI(title="Tracer Study Questionnaire Service")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.mount("/static", StaticFiles(directory="app/static"), name="static")


def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


# Period endpoints
@app.post("/periods", response_model=schemas.PeriodOut)
def create_period(period: schemas.PeriodCreate, db: Session = Depends(get_db)):
    if period.status == models.PeriodStatus.active:
        db.query(models.Period).filter(models.Period.status == models.PeriodStatus.active).update(
            {models.Period.status: models.PeriodStatus.draft}
        )
    db_period = models.Period(name=period.name, status=period.status)
    db.add(db_period)
    db.commit()
    db.refresh(db_period)
    return db_period


@app.get("/periods", response_model=list[schemas.PeriodOut])
def list_periods(db: Session = Depends(get_db)):
    return db.query(models.Period).order_by(models.Period.created_at.desc()).all()


@app.patch("/periods/{period_id}", response_model=schemas.PeriodOut)
def update_period(period_id: int, payload: schemas.PeriodUpdate, db: Session = Depends(get_db)):
    db_period = db.query(models.Period).get(period_id)
    if not db_period:
        raise HTTPException(status_code=404, detail="Period not found")

    if payload.status == models.PeriodStatus.active:
        db.query(models.Period).filter(
            models.Period.status == models.PeriodStatus.active, models.Period.id != period_id
        ).update({models.Period.status: models.PeriodStatus.draft})

    if payload.name is not None:
        db_period.name = payload.name
    if payload.status is not None:
        db_period.status = payload.status

    db.commit()
    db.refresh(db_period)
    return db_period


@app.delete("/periods/{period_id}")
def delete_period(period_id: int, db: Session = Depends(get_db)):
    deleted = db.query(models.Period).filter(models.Period.id == period_id).delete()
    if not deleted:
        raise HTTPException(status_code=404, detail="Period not found")
    db.commit()
    return {"status": "deleted"}


# Questionnaire endpoints
@app.get("/periods/{period_id}/questionnaires", response_model=list[schemas.QuestionnaireOut])
def list_questionnaires(period_id: int, db: Session = Depends(get_db)):
    period = db.query(models.Period).get(period_id)
    if not period:
        raise HTTPException(status_code=404, detail="Period not found")
    return (
        db.query(models.QuestionnaireVersion)
        .filter(models.QuestionnaireVersion.period_id == period_id)
        .order_by(models.QuestionnaireVersion.version_number.desc())
        .all()
    )


@app.get(
    "/periods/{period_id}/questionnaires/latest", response_model=schemas.QuestionnaireOut
)
def get_latest_questionnaire(period_id: int, db: Session = Depends(get_db)):
    questionnaire = (
        db.query(models.QuestionnaireVersion)
        .filter(models.QuestionnaireVersion.period_id == period_id)
        .order_by(models.QuestionnaireVersion.version_number.desc())
        .first()
    )
    if not questionnaire:
        raise HTTPException(status_code=404, detail="No questionnaire for this period yet")
    return questionnaire


@app.post(
    "/periods/{period_id}/questionnaires", response_model=schemas.QuestionnaireOut, status_code=201
)
def create_questionnaire(period_id: int, payload: schemas.QuestionnaireCreate, db: Session = Depends(get_db)):
    period = db.query(models.Period).get(period_id)
    if not period:
        raise HTTPException(status_code=404, detail="Period not found")

    latest_version = (
        db.query(func.max(models.QuestionnaireVersion.version_number))
        .filter(models.QuestionnaireVersion.period_id == period_id)
        .scalar()
    )
    next_version = (latest_version or 0) + 1

    # Base questions on previous version if requested
    base_questions = []
    if payload.clone_latest and latest_version:
        latest = (
            db.query(models.QuestionnaireVersion)
            .filter(
                models.QuestionnaireVersion.period_id == period_id,
                models.QuestionnaireVersion.version_number == latest_version,
            )
            .first()
        )
        base_questions = [
            schemas.QuestionCreate(
                text=q.text,
                type=q.type,
                required=q.required,
                position=q.position,
                metadata_json=q.metadata_json or {},
            )
            for q in latest.questions
        ]

    incoming_questions = payload.questions or base_questions

    version = models.QuestionnaireVersion(
        period_id=period_id,
        version_number=next_version,
        title=payload.title,
        description=payload.description,
    )
    db.add(version)
    db.flush()

    for idx, question_payload in enumerate(incoming_questions):
        question = models.Question(
            questionnaire_id=version.id,
            text=question_payload.text,
            type=question_payload.type,
            required=question_payload.required,
            position=question_payload.position or idx,
            metadata_json=question_payload.metadata_json or {},
        )
        db.add(question)

    db.commit()
    db.refresh(version)
    return version


@app.post(
    "/periods/{period_id}/questionnaires/clone",
    response_model=schemas.QuestionnaireOut,
    status_code=201,
)
def clone_latest_questionnaire(period_id: int, db: Session = Depends(get_db)):
    latest = (
        db.query(models.QuestionnaireVersion)
        .filter(models.QuestionnaireVersion.period_id == period_id)
        .order_by(models.QuestionnaireVersion.version_number.desc())
        .first()
    )
    if not latest:
        raise HTTPException(status_code=404, detail="No questionnaire to clone")

    payload = schemas.QuestionnaireCreate(
        title=latest.title,
        description=latest.description,
        clone_latest=True,
        questions=[],
    )
    return create_questionnaire(period_id=period_id, payload=payload, db=db)


@app.get("/")
def root():
    return {"message": "Tracer Study Questionnaire API", "docs": "/docs", "ui": "/static/index.html"}
