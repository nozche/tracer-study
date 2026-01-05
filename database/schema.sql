CREATE TABLE IF NOT EXISTS alumni (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT,
    whatsapp_number TEXT,
    tracer_link TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS invitations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    alumni_id INTEGER NOT NULL,
    token TEXT NOT NULL UNIQUE,
    tracer_url TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    delivery_channel TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME,
    CONSTRAINT fk_alumni FOREIGN KEY (alumni_id) REFERENCES alumni(id)
);

CREATE TABLE IF NOT EXISTS queue_jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invitation_id INTEGER NOT NULL,
    channel TEXT NOT NULL,
    payload TEXT NOT NULL,
    attempts INTEGER NOT NULL DEFAULT 0,
    available_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reserved_at DATETIME,
    completed_at DATETIME,
    failed_at DATETIME,
    last_error TEXT,
    CONSTRAINT fk_invitation FOREIGN KEY (invitation_id) REFERENCES invitations(id)
);

CREATE TABLE IF NOT EXISTS delivery_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invitation_id INTEGER NOT NULL,
    channel TEXT NOT NULL,
    status TEXT NOT NULL,
    metadata TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_delivery_invitation FOREIGN KEY (invitation_id) REFERENCES invitations(id)
);
