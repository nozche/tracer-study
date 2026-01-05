from fastapi import FastAPI

from app.database import init_db
from app.routers import profiles, users

app = FastAPI(title="Tracer Study API")


@app.on_event("startup")
def on_startup() -> None:
    init_db()


@app.get("/")
def health_check() -> dict:
    return {"status": "ok"}


app.include_router(users.router)
app.include_router(profiles.router)

