from typing import Annotated, Literal

from fastapi import Depends, FastAPI, Query
from fastapi.responses import JSONResponse, StreamingResponse

from .database import get_connection, initialize_db
from . import services

app = FastAPI(title="Tracer Study Dashboard")


@app.on_event("startup")
def startup() -> None:
    initialize_db()


def get_db():
    conn = get_connection()
    try:
        yield conn
    finally:
        conn.close()


@app.get("/", response_class=JSONResponse)
def root():
    return {"message": "Tracer Study dashboard and export service is running."}


@app.get("/dashboard")
def dashboard(db=Depends(get_db)):
    return services.dashboard(db)


@app.get("/responses")
def list_responses(
    faculty: str | None = Query(default=None, description="Nama fakultas"),
    cohort_year: int | None = Query(default=None, description="Angkatan / cohort"),
    status: str | None = Query(default=None, description="Status: not_opened, draft, submitted"),
    search: str | None = Query(default=None, description="Cari nama/email/fakultas"),
    db=Depends(get_db),
):
    return {"items": services.fetch_responses(db, faculty, cohort_year, status, search)}


@app.get("/responses/export")
def export_responses(
    faculty: str | None = Query(default=None, description="Nama fakultas"),
    cohort_year: int | None = Query(default=None, description="Angkatan / cohort"),
    status: str | None = Query(default=None, description="Status: not_opened, draft, submitted"),
    search: str | None = Query(default=None, description="Cari nama/email/fakultas"),
    fmt: Annotated[Literal["csv", "xlsx"], Query(alias="format", default="csv")] = "csv",
    db=Depends(get_db),
):
    payload, content_type = services.export_responses(db, faculty, cohort_year, status, search, fmt)
    filename = f"responses.{fmt}"
    if fmt == "csv":
        return StreamingResponse(
            iter([payload]),
            media_type=content_type,
            headers={"Content-Disposition": f"attachment; filename={filename}"},
        )
    return StreamingResponse(
        iter([payload]),
        media_type=content_type,
        headers={"Content-Disposition": f'attachment; filename="{filename}"'},
    )
