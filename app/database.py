import os
from contextlib import contextmanager
from typing import Iterator, Optional

from sqlmodel import Session, SQLModel, create_engine

DATABASE_URL = os.getenv("DATABASE_URL", "sqlite:///./app.db")
engine = create_engine(DATABASE_URL, connect_args={"check_same_thread": False})


def init_db(custom_engine: Optional[create_engine] = None) -> None:
    """Create database tables."""
    SQLModel.metadata.create_all(custom_engine or engine)


def get_session() -> Iterator[Session]:
    """FastAPI dependency that yields a database session."""
    with Session(engine) as session:
        yield session


@contextmanager
def session_scope(custom_engine: Optional[create_engine] = None) -> Iterator[Session]:
    """Context manager for scripts or utilities outside FastAPI."""
    with Session(custom_engine or engine) as session:
        try:
            yield session
            session.commit()
        except Exception:
            session.rollback()
            raise
