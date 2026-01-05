from typing import List

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.exc import IntegrityError
from sqlmodel import Session, select

from app.database import get_session
from app.models import Role, User
from app.schemas import UserCreate, UserRead, UserUpdate

router = APIRouter(prefix="/super-admin/users", tags=["super-admin-users"])


def _fetch_user(session: Session, user_id: int) -> User:
    user = session.get(User, user_id)
    if not user:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="User not found")
    return user


def _get_or_create_roles(session: Session, role_names: List[str]) -> List[Role]:
    roles: List[Role] = []
    for name in role_names:
        role = session.exec(select(Role).where(Role.name == name)).first()
        if not role:
            role = Role(name=name)
            session.add(role)
            session.flush()
        roles.append(role)
    return roles


def _serialize_user(user: User) -> UserRead:
    return UserRead(
        id=user.id,
        email=user.email,
        full_name=user.full_name,
        is_active=user.is_active,
        roles=[role.name for role in user.roles],
        alumni_profile=user.alumni_profile,
    )


@router.post("", response_model=UserRead, status_code=status.HTTP_201_CREATED)
def create_user(payload: UserCreate, session: Session = Depends(get_session)) -> UserRead:
    user = User(email=payload.email, full_name=payload.full_name, is_active=payload.is_active)
    if payload.roles:
        user.roles = _get_or_create_roles(session, payload.roles)

    session.add(user)
    try:
        session.commit()
        session.refresh(user)
    except IntegrityError:
        session.rollback()
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Email already exists",
        )
    return _serialize_user(user)


@router.get("", response_model=List[UserRead])
def list_users(session: Session = Depends(get_session)) -> List[UserRead]:
    users = session.exec(select(User)).all()
    return [_serialize_user(user) for user in users]


@router.get("/{user_id}", response_model=UserRead)
def get_user(user_id: int, session: Session = Depends(get_session)) -> UserRead:
    user = _fetch_user(session, user_id)
    return _serialize_user(user)


@router.patch("/{user_id}", response_model=UserRead)
def update_user(user_id: int, payload: UserUpdate, session: Session = Depends(get_session)) -> UserRead:
    user = _fetch_user(session, user_id)

    if payload.email is not None:
        user.email = payload.email
    if payload.full_name is not None:
        user.full_name = payload.full_name
    if payload.is_active is not None:
        user.is_active = payload.is_active
    if payload.roles is not None:
        user.roles = _get_or_create_roles(session, payload.roles)

    try:
        session.add(user)
        session.commit()
        session.refresh(user)
    except IntegrityError:
        session.rollback()
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Email already exists",
        )
    return _serialize_user(user)


@router.delete("/{user_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_user(user_id: int, session: Session = Depends(get_session)) -> None:
    user = _fetch_user(session, user_id)
    session.delete(user)
    session.commit()

