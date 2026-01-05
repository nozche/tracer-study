from datetime import datetime
from typing import List, Optional

from pydantic import BaseModel, Field

from .models import QuestionType, ResponseStatus


class QuestionSchema(BaseModel):
    id: int
    prompt: str
    type: QuestionType
    required: bool
    options: Optional[list] = None

    class Config:
        orm_mode = True


class FormSchema(BaseModel):
    id: int
    title: str
    active_from: datetime
    active_until: Optional[datetime]
    allow_edit_after_submit: bool
    questions: List[QuestionSchema]

    class Config:
        orm_mode = True


class AnswerPayload(BaseModel):
    question_id: int
    value: str = Field(..., description="User response captured as text representation")


class ResponsePayload(BaseModel):
    answers: List[AnswerPayload]
    submit: bool = False


class AnswerSchema(BaseModel):
    question_id: int
    value: Optional[str]

    class Config:
        orm_mode = True


class ResponseSchema(BaseModel):
    id: int
    status: ResponseStatus
    updated_at: datetime
    answers: List[AnswerSchema]

    class Config:
        orm_mode = True


class FormWithInvitationSchema(BaseModel):
    form: FormSchema
    invitation_token: str
    invitation_id: int
    existing_response: Optional[ResponseSchema]
