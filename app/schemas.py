from datetime import datetime
from typing import Any, List, Optional

from pydantic import BaseModel, Field

from .models import PeriodStatus, QuestionType


class PeriodBase(BaseModel):
    name: str
    status: PeriodStatus = PeriodStatus.draft


class PeriodCreate(PeriodBase):
    pass


class PeriodUpdate(BaseModel):
    name: Optional[str]
    status: Optional[PeriodStatus]


class PeriodOut(PeriodBase):
    id: int
    created_at: datetime
    updated_at: Optional[datetime]

    class Config:
        orm_mode = True


class QuestionBase(BaseModel):
    text: str
    type: QuestionType
    required: bool = False
    position: int = 0
    metadata_json: dict = Field(default_factory=dict)


class QuestionCreate(QuestionBase):
    pass


class QuestionOut(QuestionBase):
    id: int

    class Config:
        orm_mode = True


class QuestionnaireBase(BaseModel):
    title: Optional[str] = None
    description: Optional[str] = None
    questions: List[QuestionCreate] = Field(default_factory=list)


class QuestionnaireCreate(QuestionnaireBase):
    clone_latest: bool = True


class QuestionnaireOut(QuestionnaireBase):
    id: int
    period_id: int
    version_number: int
    created_at: datetime
    questions: List[QuestionOut]

    class Config:
        orm_mode = True
