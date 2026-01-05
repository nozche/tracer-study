from fastapi import APIRouter, Depends, HTTPException, status
from sqlmodel import Session, select

from app.database import get_session
from app.models import AlumniProfile, LecturerProfile, StudentProfile, User
from app.schemas import (
    AlumniProfileRead,
    AlumniProfileUpdate,
    LecturerProfileBase,
    LecturerProfileRead,
    StudentProfileBase,
    StudentProfileRead,
)

router = APIRouter(tags=["profiles"])


def _require_user(session: Session, user_id: int) -> User:
    user = session.get(User, user_id)
    if not user:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="User not found")
    return user


@router.get("/alumni/{user_id}/profile", response_model=AlumniProfileRead)
def get_alumni_profile(user_id: int, session: Session = Depends(get_session)) -> AlumniProfileRead:
    _require_user(session, user_id)
    profile = session.exec(
        select(AlumniProfile).where(AlumniProfile.user_id == user_id)
    ).first()
    if not profile:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Profile not found")
    return profile


@router.put("/alumni/{user_id}/profile", response_model=AlumniProfileRead)
def upsert_alumni_profile(
    user_id: int, payload: AlumniProfileUpdate, session: Session = Depends(get_session)
) -> AlumniProfileRead:
    _require_user(session, user_id)
    profile = session.exec(
        select(AlumniProfile).where(AlumniProfile.user_id == user_id)
    ).first()

    if not profile:
        profile = AlumniProfile(user_id=user_id)
        session.add(profile)

    for field, value in payload.dict(exclude_unset=True).items():
        setattr(profile, field, value)

    session.add(profile)
    session.commit()
    session.refresh(profile)
    return profile


@router.put("/students/{user_id}/profile", response_model=StudentProfileRead)
def upsert_student_profile(
    user_id: int, payload: StudentProfileBase, session: Session = Depends(get_session)
) -> StudentProfileRead:
    _require_user(session, user_id)
    profile = session.exec(
        select(StudentProfile).where(StudentProfile.user_id == user_id)
    ).first()

    if not profile:
        profile = StudentProfile(user_id=user_id, **payload.dict())
    else:
        for field, value in payload.dict().items():
            setattr(profile, field, value)

    session.add(profile)
    session.commit()
    session.refresh(profile)
    return profile


@router.put("/lecturers/{user_id}/profile", response_model=LecturerProfileRead)
def upsert_lecturer_profile(
    user_id: int, payload: LecturerProfileBase, session: Session = Depends(get_session)
) -> LecturerProfileRead:
    _require_user(session, user_id)
    profile = session.exec(
        select(LecturerProfile).where(LecturerProfile.user_id == user_id)
    ).first()

    if not profile:
        profile = LecturerProfile(user_id=user_id, **payload.dict())
    else:
        for field, value in payload.dict().items():
            setattr(profile, field, value)

    session.add(profile)
    session.commit()
    session.refresh(profile)
    return profile

