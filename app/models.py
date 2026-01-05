import enum
from datetime import datetime
from typing import Any

from sqlalchemy import Column, DateTime, Enum, ForeignKey, Integer, JSON, String, Boolean
from sqlalchemy.orm import relationship

from .database import Base


class PeriodStatus(str, enum.Enum):
    draft = "draft"
    active = "active"
    closed = "closed"


class Period(Base):
    __tablename__ = "periods"

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String, nullable=False, unique=True)
    status = Column(Enum(PeriodStatus), default=PeriodStatus.draft, nullable=False)
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    questionnaires = relationship("QuestionnaireVersion", back_populates="period")


class QuestionnaireVersion(Base):
    __tablename__ = "questionnaire_versions"

    id = Column(Integer, primary_key=True, index=True)
    period_id = Column(Integer, ForeignKey("periods.id"), nullable=False)
    version_number = Column(Integer, nullable=False)
    title = Column(String, nullable=True)
    description = Column(String, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)

    period = relationship("Period", back_populates="questionnaires")
    questions = relationship("Question", back_populates="questionnaire", cascade="all, delete-orphan")


class QuestionType(str, enum.Enum):
    single = "single"
    multiple = "multiple"
    text = "text"
    scale = "scale"
    matrix = "matrix"


class Question(Base):
    __tablename__ = "questions"

    id = Column(Integer, primary_key=True, index=True)
    questionnaire_id = Column(Integer, ForeignKey("questionnaire_versions.id"), nullable=False)
    text = Column(String, nullable=False)
    type = Column(Enum(QuestionType), nullable=False)
    required = Column(Boolean, default=False, nullable=False)
    position = Column(Integer, nullable=False, default=0)
    metadata_json = Column(JSON, nullable=False, default=dict)

    questionnaire = relationship("QuestionnaireVersion", back_populates="questions")
