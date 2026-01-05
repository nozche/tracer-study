from __future__ import annotations

import io
import sqlite3
from typing import Any, Dict, Iterable, List, Tuple

from fastapi import HTTPException
from openpyxl import Workbook


def _build_filters(
    faculty: str | None, cohort_year: int | None, status: str | None, search: str | None
) -> Tuple[str, List[Any]]:
    clauses: List[str] = []
    params: List[Any] = []

    if faculty:
        clauses.append("faculty_name = ?")
        params.append(faculty)
    if cohort_year:
        clauses.append("cohort_year = ?")
        params.append(cohort_year)
    if status:
        clauses.append("status = ?")
        params.append(status)
    if search:
        search_term = f"%{search.lower()}%"
        clauses.append(
            "("
            "lower(student_name) LIKE ? OR "
            "lower(student_email) LIKE ? OR "
            "lower(faculty_name) LIKE ?"
            ")"
        )
        params.extend([search_term, search_term, search_term])

    where_clause = f" WHERE {' AND '.join(clauses)}" if clauses else ""
    return where_clause, params


def fetch_responses(
    conn: sqlite3.Connection,
    faculty: str | None,
    cohort_year: int | None,
    status: str | None,
    search: str | None,
) -> List[Dict[str, Any]]:
    where_clause, params = _build_filters(faculty, cohort_year, status, search)
    query = f"""
        SELECT response_id, status, started_at, submitted_at, answers_count,
               invitation_id, sent_at, student_id, student_name, student_email,
               cohort_year, faculty_name
        FROM responses_view
        {where_clause}
        ORDER BY submitted_at IS NULL, submitted_at DESC, started_at IS NULL, started_at DESC, student_name ASC;
    """

    rows = conn.execute(query, params).fetchall()
    return [dict(row) for row in rows]


def dashboard(conn: sqlite3.Connection) -> Dict[str, Any]:
    total_invitations = conn.execute("SELECT COUNT(*) FROM invitations;").fetchone()[0]
    submitted_responses = conn.execute(
        "SELECT COUNT(*) FROM responses WHERE status = 'submitted';"
    ).fetchone()[0]

    status_counts = {
        "belum_buka": 0,
        "draft": 0,
        "submitted": 0,
    }
    for row in conn.execute(
        """
        SELECT status, COUNT(*) AS count
        FROM responses
        GROUP BY status;
        """
    ):
        key = "belum_buka" if row["status"] == "not_opened" else row["status"]
        status_counts[key] = row["count"]

    progress_by_faculty = conn.execute(
        """
        SELECT
            faculty_name,
            COUNT(*) AS invitations,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS submitted,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft,
            SUM(CASE WHEN status = 'not_opened' THEN 1 ELSE 0 END) AS belum_buka
        FROM responses_view
        GROUP BY faculty_name
        ORDER BY faculty_name;
        """
    ).fetchall()

    progress_by_cohort = conn.execute(
        """
        SELECT
            cohort_year,
            COUNT(*) AS invitations,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS submitted,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft,
            SUM(CASE WHEN status = 'not_opened' THEN 1 ELSE 0 END) AS belum_buka
        FROM responses_view
        GROUP BY cohort_year
        ORDER BY cohort_year;
        """
    ).fetchall()

    return {
        "total_invitations": total_invitations,
        "responses_received": submitted_responses,
        "status_summary": status_counts,
        "progress_by_faculty": [dict(row) for row in progress_by_faculty],
        "progress_by_cohort": [dict(row) for row in progress_by_cohort],
    }


def export_responses(
    conn: sqlite3.Connection,
    faculty: str | None,
    cohort_year: int | None,
    status: str | None,
    search: str | None,
    fmt: str,
) -> Tuple[bytes | str, str]:
    rows = fetch_responses(conn, faculty, cohort_year, status, search)
    if fmt == "csv":
        return _to_csv(rows), "text/csv"
    if fmt == "xlsx":
        return _to_excel(rows), "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    raise HTTPException(status_code=400, detail="Unsupported format. Use 'csv' or 'xlsx'.")


def _to_csv(rows: Iterable[Dict[str, Any]]) -> str:
    buffer = io.StringIO()
    headers = [
        "response_id",
        "status",
        "started_at",
        "submitted_at",
        "answers_count",
        "invitation_id",
        "sent_at",
        "student_id",
        "student_name",
        "student_email",
        "cohort_year",
        "faculty_name",
    ]

    buffer.write(",".join(headers) + "\n")
    for row in rows:
        buffer.write(
            ",".join(
                [
                    _escape_csv(row.get("response_id")),
                    _escape_csv(row.get("status")),
                    _escape_csv(row.get("started_at")),
                    _escape_csv(row.get("submitted_at")),
                    _escape_csv(row.get("answers_count")),
                    _escape_csv(row.get("invitation_id")),
                    _escape_csv(row.get("sent_at")),
                    _escape_csv(row.get("student_id")),
                    _escape_csv(row.get("student_name")),
                    _escape_csv(row.get("student_email")),
                    _escape_csv(row.get("cohort_year")),
                    _escape_csv(row.get("faculty_name")),
                ]
            )
            + "\n"
        )
    return buffer.getvalue()


def _escape_csv(value: Any) -> str:
    if value is None:
        return ""
    text = str(value)
    if any(char in text for char in (",", "\"", "\n")):
        text = '"' + text.replace('"', '""') + '"'
    return text


def _to_excel(rows: Iterable[Dict[str, Any]]) -> bytes:
    workbook = Workbook()
    sheet = workbook.active
    headers = [
        "response_id",
        "status",
        "started_at",
        "submitted_at",
        "answers_count",
        "invitation_id",
        "sent_at",
        "student_id",
        "student_name",
        "student_email",
        "cohort_year",
        "faculty_name",
    ]
    sheet.append(headers)

    for row in rows:
        sheet.append([row.get(header) for header in headers])

    stream = io.BytesIO()
    workbook.save(stream)
    stream.seek(0)
    return stream.read()
