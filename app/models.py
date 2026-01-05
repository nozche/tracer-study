from typing import List, Optional

from pydantic import EmailStr
from sqlmodel import Column, Field, Relationship, SQLModel, String


class UserRoleLink(SQLModel, table=True):
    __tablename__ = "user_roles"
    user_id: Optional[int] = Field(
        default=None, foreign_key="users.id", primary_key=True, index=True
    )
    role_id: Optional[int] = Field(
        default=None, foreign_key="roles.id", primary_key=True, index=True
    )


class Role(SQLModel, table=True):
    __tablename__ = "roles"
    id: Optional[int] = Field(default=None, primary_key=True)
    name: str = Field(
        sa_column=Column(String, unique=True, index=True, nullable=False)
    )
    users: List["User"] = Relationship(
        back_populates="roles", link_model=UserRoleLink
    )


class User(SQLModel, table=True):
    __tablename__ = "users"
    id: Optional[int] = Field(default=None, primary_key=True)
    email: EmailStr = Field(
        sa_column=Column(String, unique=True, index=True, nullable=False)
    )
    full_name: str
    is_active: bool = Field(default=True)
    roles: List[Role] = Relationship(
        back_populates="users", link_model=UserRoleLink
    )
    alumni_profile: Optional["AlumniProfile"] = Relationship(
        back_populates="user", sa_relationship_kwargs={"uselist": False}
    )
    student_profile: Optional["StudentProfile"] = Relationship(
        back_populates="user", sa_relationship_kwargs={"uselist": False}
    )
    lecturer_profile: Optional["LecturerProfile"] = Relationship(
        back_populates="user", sa_relationship_kwargs={"uselist": False}
    )


class AlumniProfile(SQLModel, table=True):
    __tablename__ = "alumni_profiles"
    id: Optional[int] = Field(default=None, primary_key=True)
    user_id: int = Field(foreign_key="users.id", unique=True, nullable=False)
    cohort_year: Optional[int] = Field(
        default=None, description="Angkatan atau tahun masuk"
    )
    program: Optional[str] = Field(default=None, description="Program studi")
    job_title: Optional[str] = Field(default=None, description="Pekerjaan saat ini")
    location: Optional[str] = Field(default=None, description="Lokasi kerja")
    salary_range: Optional[str] = Field(
        default=None, description="Rentang gaji atau level penghasilan"
    )
    user: Optional[User] = Relationship(back_populates="alumni_profile")


class StudentProfile(SQLModel, table=True):
    __tablename__ = "student_profiles"
    id: Optional[int] = Field(default=None, primary_key=True)
    user_id: int = Field(foreign_key="users.id", unique=True, nullable=False)
    student_number: str = Field(index=True, description="Nomor induk mahasiswa")
    program: Optional[str] = Field(default=None, description="Program studi")
    entry_year: Optional[int] = Field(default=None, description="Tahun masuk")
    user: Optional[User] = Relationship(back_populates="student_profile")


class LecturerProfile(SQLModel, table=True):
    __tablename__ = "lecturer_profiles"
    id: Optional[int] = Field(default=None, primary_key=True)
    user_id: int = Field(foreign_key="users.id", unique=True, nullable=False)
    employee_id: str = Field(index=True, description="NIP/NIDN")
    department: Optional[str] = Field(default=None, description="Departemen atau prodi")
    position: Optional[str] = Field(default=None, description="Jabatan")
    user: Optional[User] = Relationship(back_populates="lecturer_profile")

