import sqlite3
from pathlib import Path
from typing import Iterable

DB_PATH = Path(__file__).resolve().parent / "tracer.db"


def get_connection() -> sqlite3.Connection:
    connection = sqlite3.connect(DB_PATH)
    connection.row_factory = sqlite3.Row
    return connection


def initialize_db() -> None:
    DB_PATH.parent.mkdir(parents=True, exist_ok=True)
    with get_connection() as conn:
        conn.executescript(
            """
            PRAGMA foreign_keys = ON;

            CREATE TABLE IF NOT EXISTS faculties (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE
            );

            CREATE TABLE IF NOT EXISTS students (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                faculty_id INTEGER NOT NULL,
                cohort_year INTEGER NOT NULL,
                FOREIGN KEY (faculty_id) REFERENCES faculties (id)
            );

            CREATE TABLE IF NOT EXISTS invitations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL,
                sent_at TEXT NOT NULL,
                FOREIGN KEY (student_id) REFERENCES students (id)
            );

            CREATE TABLE IF NOT EXISTS responses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invitation_id INTEGER NOT NULL UNIQUE,
                status TEXT NOT NULL CHECK(status IN ('not_opened', 'draft', 'submitted')),
                started_at TEXT,
                submitted_at TEXT,
                answers_count INTEGER DEFAULT 0,
                FOREIGN KEY (invitation_id) REFERENCES invitations (id)
            );

            CREATE VIEW IF NOT EXISTS responses_view AS
            SELECT
                r.id AS response_id,
                r.status,
                r.started_at,
                r.submitted_at,
                r.answers_count,
                i.id AS invitation_id,
                i.sent_at,
                s.id AS student_id,
                s.name AS student_name,
                s.email AS student_email,
                s.cohort_year,
                f.name AS faculty_name
            FROM responses r
            JOIN invitations i ON i.id = r.invitation_id
            JOIN students s ON s.id = i.student_id
            JOIN faculties f ON f.id = s.faculty_id;
            """
        )

        if not _has_seed_data(conn):
            _seed(conn)


def _has_seed_data(conn: sqlite3.Connection) -> bool:
    count = conn.execute("SELECT COUNT(*) FROM faculties;").fetchone()[0]
    return count > 0


def _seed(conn: sqlite3.Connection) -> None:
    faculties = ("Teknik", "Ekonomi", "Hukum")
    conn.executemany("INSERT INTO faculties (name) VALUES (?);", ((name,) for name in faculties))

    students = [
        ("Andi", "andi@alumni.ac.id", "Teknik", 2019),
        ("Budi", "budi@alumni.ac.id", "Teknik", 2020),
        ("Cici", "cici@alumni.ac.id", "Ekonomi", 2019),
        ("Dina", "dina@alumni.ac.id", "Ekonomi", 2020),
        ("Eka", "eka@alumni.ac.id", "Hukum", 2019),
        ("Farah", "farah@alumni.ac.id", "Hukum", 2020),
    ]

    faculty_map = {row["name"]: row["id"] for row in conn.execute("SELECT id, name FROM faculties;")}
    for name, email, faculty, cohort_year in students:
        conn.execute(
            "INSERT INTO students (name, email, faculty_id, cohort_year) VALUES (?, ?, ?, ?);",
            (name, email, faculty_map[faculty], cohort_year),
        )

    invitations = []
    for idx, student_id in enumerate(_flatten(conn.execute("SELECT id FROM students;"))):
        invitations.append((student_id, f"2024-05-{15 + idx:02d}T08:00:00Z"))

    conn.executemany("INSERT INTO invitations (student_id, sent_at) VALUES (?, ?);", invitations)

    responses = [
        ("not_opened", None, None, 0),
        ("draft", "2024-05-17T09:15:00Z", None, 3),
        ("submitted", "2024-05-16T10:00:00Z", "2024-05-17T11:30:00Z", 12),
        ("submitted", "2024-05-18T07:45:00Z", "2024-05-18T08:10:00Z", 9),
        ("draft", "2024-05-16T08:20:00Z", None, 5),
        ("submitted", "2024-05-19T06:30:00Z", "2024-05-19T07:05:00Z", 10),
    ]

    invitation_ids = list(_flatten(conn.execute("SELECT id FROM invitations ORDER BY id;")))
    conn.executemany(
        """
        INSERT INTO responses (invitation_id, status, started_at, submitted_at, answers_count)
        VALUES (?, ?, ?, ?, ?);
        """,
        (
            (invitation_id, status, started_at, submitted_at, answers_count)
            for invitation_id, (status, started_at, submitted_at, answers_count) in zip(invitation_ids, responses)
        ),
    )


def _flatten(rows: Iterable[sqlite3.Row]) -> Iterable[int]:
    for row in rows:
        yield row[0]
