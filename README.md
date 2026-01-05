# tracer-study

Prototype utilities for managing tracer study invitations with tokenized links and queued delivery.

## Setup
1. Ensure PHP 8+ and SQLite are available.
2. Install dependencies (none required beyond core PHP).
3. Initialize the database:
   ```bash
   php bin/init_db.php
   ```
   You can override the SQLite path via `TRACER_DB_PATH` and the public form URL via `TRACER_BASE_URL`.

## Usage
Create an invitation (queues a send job):
```bash
php bin/create_invitation.php --name="Jane Doe" --email=jane@example.com --wa=62812345 --channel=email
```

Process the queue once (suitable for cron/CodeIgniter Tasks):
```bash
php bin/process_queue.php
```

### Delivery notes
- `NotificationSender` contains stubs where you can wire in your mail gateway and WhatsApp API.
- Delivery attempts are logged in `delivery_logs`; retries are scheduled with a 5-minute delay up to 3 attempts.
- Invitation status moves from `pending` → `sent` or `pending_retry`.

## Database schema
Defined in `database/schema.sql` and includes:
- `alumni`: contact details and generated tracer link.
- `invitations`: tokens and per-recipient tracer URLs.
- `queue_jobs`: queued delivery tasks with attempt tracking.
- `delivery_logs`: immutable audit of send outcomes and metadata.
