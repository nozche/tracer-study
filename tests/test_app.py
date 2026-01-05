import pytest
from fastapi.testclient import TestClient
from sqlmodel import Session, SQLModel, create_engine

from app.database import get_session, init_db
from app.main import app
from app.models import User


@pytest.fixture(name="session")
def session_fixture():
    engine = create_engine("sqlite://", connect_args={"check_same_thread": False})
    SQLModel.metadata.create_all(engine)
    with Session(engine) as session:
        yield session


@pytest.fixture(name="client")
def client_fixture(session: Session):
    def get_session_override():
        yield session

    app.dependency_overrides[get_session] = get_session_override
    return TestClient(app)


def test_create_user_with_roles(client: TestClient):
    response = client.post(
        "/super-admin/users",
        json={
            "email": "admin@example.com",
            "full_name": "Admin Example",
            "roles": ["super_admin", "editor"],
        },
    )
    assert response.status_code == 201, response.text
    data = response.json()
    assert set(data["roles"]) == {"super_admin", "editor"}
    assert data["full_name"] == "Admin Example"


def test_update_user_and_assign_roles(client: TestClient):
    create = client.post(
        "/super-admin/users",
        json={"email": "user@example.com", "full_name": "User Example"},
    )
    user_id = create.json()["id"]

    response = client.patch(
        f"/super-admin/users/{user_id}",
        json={"roles": ["alumni", "dosen"], "full_name": "Updated"},
    )
    assert response.status_code == 200
    data = response.json()
    assert set(data["roles"]) == {"alumni", "dosen"}
    assert data["full_name"] == "Updated"


def test_upsert_alumni_profile(client: TestClient):
    create = client.post(
        "/super-admin/users",
        json={"email": "alumni@example.com", "full_name": "Alumni Example"},
    )
    user_id = create.json()["id"]

    update = client.put(
        f"/alumni/{user_id}/profile",
        json={
            "cohort_year": 2018,
            "program": "Informatika",
            "job_title": "Engineer",
            "location": "Jakarta",
            "salary_range": "8-12jt",
        },
    )
    assert update.status_code == 200
    data = update.json()
    assert data["cohort_year"] == 2018
    assert data["program"] == "Informatika"


def test_student_and_lecturer_profiles(client: TestClient):
    create = client.post(
        "/super-admin/users",
        json={"email": "validator@example.com", "full_name": "Validator User"},
    )
    user_id = create.json()["id"]

    student_resp = client.put(
        f"/students/{user_id}/profile",
        json={"student_number": "NIM123", "program": "Sistem Informasi", "entry_year": 2020},
    )
    assert student_resp.status_code == 200
    assert student_resp.json()["student_number"] == "NIM123"

    lecturer_resp = client.put(
        f"/lecturers/{user_id}/profile",
        json={"employee_id": "NIDN001", "department": "TI", "position": "Dosen"},
    )
    assert lecturer_resp.status_code == 200
    assert lecturer_resp.json()["employee_id"] == "NIDN001"

