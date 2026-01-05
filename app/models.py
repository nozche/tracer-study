from datetime import datetime
from enum import Enum
from typing import List, Optional

from sqlalchemy import (
    JSON,
    Boolean,
    Column,
    DateTime,
    ForeignKey,
    Integer,
    String,
    UniqueConstraint,
)
from sqlalchemy.orm import Mapped, mapped_column, relationship

from .database import Base


class QuestionType(str, Enum):
    short_text = "short_text"
    long_text = "long_text"
    single_choice = "single_choice"
    multi_choice = "multi_choice"


class Form(Base):
    __tablename__ = "forms"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, index=True)
    title: Mapped[str] = mapped_column(String, nullable=False)
    active_from: Mapped[datetime] = mapped_column(DateTime, nullable=False, default=datetime.utcnow)
    active_until: Mapped[Optional[datetime]] = mapped_column(DateTime, nullable=True)
    allow_edit_after_submit: Mapped[bool] = mapped_column(Boolean, default=False)

    questions: Mapped[List["Question"]] = relationship("Question", back_populates="form")
    invitations: Mapped[List["Invitation"]] = relationship("Invitation", back_populates="form")


class Invitation(Base):
    __tablename__ = "invitations"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, index=True)
    alumni_name: Mapped[str] = mapped_column(String, nullable=False)
    token: Mapped[str] = mapped_column(String, unique=True, nullable=False, index=True)
    form_id: Mapped[int] = mapped_column(Integer, ForeignKey("forms.id"), nullable=False)

    form: Mapped[Form] = relationship("Form", back_populates="invitations")
    responses: Mapped[List["Response"]] = relationship("Response", back_populates="invitation")


class Question(Base):
    __tablename__ = "questions"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, index=True)
    form_id: Mapped[int] = mapped_column(Integer, ForeignKey("forms.id"), nullable=False, index=True)
    prompt: Mapped[str] = mapped_column(String, nullable=False)
    type: Mapped[QuestionType] = mapped_column(String, nullable=False, default=QuestionType.short_text)
    required: Mapped[bool] = mapped_column(Boolean, default=False)
    options: Mapped[Optional[list]] = mapped_column(JSON, nullable=True)

    form: Mapped[Form] = relationship("Form", back_populates="questions")
    answers: Mapped[List["Answer"]] = relationship("Answer", back_populates="question")


class ResponseStatus(str, Enum):
    draft = "draft"
    submitted = "submitted"


class Response(Base):
    __tablename__ = "responses"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, index=True)
    form_id: Mapped[int] = mapped_column(Integer, ForeignKey("forms.id"), nullable=False)
    invitation_id: Mapped[int] = mapped_column(Integer, ForeignKey("invitations.id"), nullable=False, unique=True)
    status: Mapped[ResponseStatus] = mapped_column(String, default=ResponseStatus.draft)
    updated_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    form: Mapped[Form] = relationship("Form")
    invitation: Mapped[Invitation] = relationship("Invitation", back_populates="responses")
    answers: Mapped[List["Answer"]] = relationship("Answer", back_populates="response", cascade="all, delete-orphan")


class Answer(Base):
    __tablename__ = "answers"
    __table_args__ = (UniqueConstraint("response_id", "question_id", name="uq_response_question"),)

    id: Mapped[int] = mapped_column(Integer, primary_key=True, index=True)
    response_id: Mapped[int] = mapped_column(Integer, ForeignKey("responses.id"), nullable=False)
    question_id: Mapped[int] = mapped_column(Integer, ForeignKey("questions.id"), nullable=False)
    value: Mapped[str] = mapped_column(String, nullable=True)

    response: Mapped[Response] = relationship("Response", back_populates="answers")
    question: Mapped[Question] = relationship("Question", back_populates="answers")
