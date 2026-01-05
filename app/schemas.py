from typing import List, Optional

from pydantic import BaseModel, EmailStr


class AlumniProfileBase(BaseModel):
    cohort_year: Optional[int] = None
    program: Optional[str] = None
    job_title: Optional[str] = None
    location: Optional[str] = None
    salary_range: Optional[str] = None


class AlumniProfileRead(AlumniProfileBase):
    id: int
    user_id: int

    class Config:
        orm_mode = True


class AlumniProfileUpdate(AlumniProfileBase):
    pass


class StudentProfileBase(BaseModel):
    student_number: str
    program: Optional[str] = None
    entry_year: Optional[int] = None


class StudentProfileRead(StudentProfileBase):
    id: int
    user_id: int

    class Config:
        orm_mode = True


class LecturerProfileBase(BaseModel):
    employee_id: str
    department: Optional[str] = None
    position: Optional[str] = None


class LecturerProfileRead(LecturerProfileBase):
    id: int
    user_id: int

    class Config:
        orm_mode = True


class UserBase(BaseModel):
    email: EmailStr
    full_name: str
    is_active: bool = True


class UserCreate(UserBase):
    roles: Optional[List[str]] = None


class UserUpdate(BaseModel):
    email: Optional[EmailStr] = None
    full_name: Optional[str] = None
    is_active: Optional[bool] = None
    roles: Optional[List[str]] = None


class UserRead(UserBase):
    id: int
    roles: List[str] = []
    alumni_profile: Optional[AlumniProfileRead] = None

    class Config:
        orm_mode = True

