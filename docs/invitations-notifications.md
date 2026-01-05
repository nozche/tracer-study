# Invitations & Notifications Implementation Plan

This document outlines how to deliver tracer study invitations via email and WhatsApp with tokenized links, queued delivery, and robust logging/retry behavior using CodeIgniter Tasks/cron.

## Data Model

Proposed tables (PostgreSQL/MySQL compatible). Adjust column types as needed for your DB engine.

### `alumni`
Reference-only (assumes existing data). Required columns for downstream features:
- `id` (PK)
- `email`, `phone_number`
- `full_name`

### `tracer_invites`
Stores invitation tokens and link context per alumni.
```sql
CREATE TABLE tracer_invites (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    alumni_id BIGINT NOT NULL,
    token CHAR(32) NOT NULL UNIQUE,
    tracer_link VARCHAR(512) NOT NULL,
    expires_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(128) NULL,
    INDEX idx_tracer_invites_alumni (alumni_id),
    CONSTRAINT fk_tracer_invites_alumni FOREIGN KEY (alumni_id) REFERENCES alumni(id)
);
```

### `notification_queue`
Holds pending deliveries for email/WhatsApp.
```sql
CREATE TABLE notification_queue (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    invite_id BIGINT NOT NULL,
    channel ENUM('email','whatsapp') NOT NULL,
    payload JSON NOT NULL,
    status ENUM('pending','sending','sent','failed','abandoned') NOT NULL DEFAULT 'pending',
    attempts INT NOT NULL DEFAULT 0,
    max_attempts INT NOT NULL DEFAULT 3,
    next_attempt_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_error TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_notification_queue_status_next (status, next_attempt_at),
    CONSTRAINT fk_notification_queue_invite FOREIGN KEY (invite_id) REFERENCES tracer_invites(id)
);
```

### `notification_attempts`
Stores metadata for every send attempt (per channel) to support auditing.
```sql
CREATE TABLE notification_attempts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    queue_id BIGINT NOT NULL,
    attempt_number INT NOT NULL,
    status ENUM('success','failed') NOT NULL,
    provider_message_id VARCHAR(128) NULL,
    provider_response JSON NULL,
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    error_message TEXT NULL,
    latency_ms INT NULL,
    CONSTRAINT fk_notification_attempts_queue FOREIGN KEY (queue_id) REFERENCES notification_queue(id)
);
```

## Token & Link Generation

- Generate a per-alumni token when creating a tracer invite.
- Example PHP (CodeIgniter 4):
```php
helper('text');

public function createInviteForAlumni(int $alumniId): array
{
    $token = bin2hex(random_bytes(16));
    $tracerLink = site_url('tracer/fill?token=' . $token);

    $inviteId = $this->tracerInviteModel->insert([
        'alumni_id'   => $alumniId,
        'token'       => $token,
        'tracer_link' => $tracerLink,
    ], true);

    // Enqueue email + WhatsApp deliveries
    $this->notificationQueueModel->insertBatch([
        [
            'invite_id' => $inviteId,
            'channel'   => 'email',
            'payload'   => [
                'to'      => $alumniEmail,
                'subject' => 'Tracer Study Invitation',
                'body'    => view('emails/tracer_invite', ['name' => $alumniName, 'link' => $tracerLink]),
            ],
        ],
        [
            'invite_id' => $inviteId,
            'channel'   => 'whatsapp',
            'payload'   => [
                'to'   => $alumniPhone,
                'text' => "Halo {$alumniName}, isi tracer di {$tracerLink}",
            ],
        ],
    ]);

    return ['token' => $token, 'link' => $tracerLink];
}
```

## Queue Processing (CodeIgniter Tasks / Cron)

- Run periodically via `php spark tasks:run` (CI4) or a cron that calls the CLI command below.
- CLI Task example (simplified):
```php
// app/Commands/SendNotifications.php
public function run()
{
    $jobs = $this->notificationQueueModel
        ->where('status', 'pending')
        ->where('next_attempt_at <=', date('Y-m-d H:i:s'))
        ->limit(50)
        ->findAll();

    foreach ($jobs as $job) {
        $this->processJob($job);
    }
}
```

### `processJob` logic
1. Mark row as `sending`, increment `attempts`.
2. Dispatch to the correct gateway based on `channel`.
3. Record success/failure in `notification_attempts` with provider metadata (message IDs, payloads, errors, latency).
4. On success → update queue row to `sent`.
5. On failure → set `status` back to `pending`, compute `next_attempt_at = NOW() + retry_interval`, and store `last_error`. If `attempts >= max_attempts`, mark as `abandoned`.

### Retry interval suggestion
- Use exponential backoff capped at 1 hour (e.g., 1m, 5m, 15m, 60m).

## Channel Delivery Integration

### Email (SMTP/Mail Gateway)
- Configure `Email` service once; use HTML template `emails/tracer_invite`.
- Gateway response should capture `messageId` or provider trace ID for logging.
- Example:
```php
$email = service('email');
$email->setTo($payload['to']);
$email->setSubject($payload['subject']);
$email->setMessage($payload['body']);
$email->send();
$providerId = $email->printDebugger('headers'); // Replace with real provider ID extractor
```

### WhatsApp API
- Abstract WhatsApp client (e.g., wrapping vendor SDK or simple cURL to provider API).
- Payload includes recipient, text/template ID, and dynamic parameters (tokenized link).
- Capture provider message ID / error info for `notification_attempts.provider_message_id` and `provider_response`.

## Monitoring & Logging

- Expose admin view for `notification_queue` and `notification_attempts` with filters by `status`, `channel`, and date.
- Add structured logging (channel, queue_id, invite_id, status, error) for observability.
- Consider alerts when abandoned count exceeds threshold.

## Security

- Tokens are random 128-bit values (32 hex chars) and not guessable.
- Optional: set `expires_at` on invites and reject submissions after expiry.
- Validate that each token is tied to the correct alumni before accepting tracer responses.

## Operational Steps

1. Migrate tables above.
2. Create service/model layer for `tracer_invites`, `notification_queue`, `notification_attempts`.
3. Build CLI task `SendNotifications` and register with CodeIgniter Tasks/cron.
4. Build a controller/action to generate invitations (bulk and per-alumni) and enqueue deliveries.
5. Configure email and WhatsApp gateways; inject into queue processor.
6. Add admin UI/log views for delivery monitoring and manual retry if desired.
