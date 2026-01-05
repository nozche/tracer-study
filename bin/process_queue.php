<?php

require_once __DIR__ . '/../app/Services/QueueWorker.php';

$worker = new QueueWorker();

$result = $worker->processNext();

if ($result === null) {
    echo "No queued jobs.\n";
    exit(0);
}

if ($result['success']) {
    echo "Job completed successfully.\n";
} else {
    echo "Job failed; will retry if attempts remain.\n";
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
