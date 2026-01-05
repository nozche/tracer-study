<?php

require_once __DIR__ . '/../app/Database.php';

$db = Database::connection();
$schema = file_get_contents(__DIR__ . '/../database/schema.sql');

$db->exec($schema);

$dbPath = getenv('TRACER_DB_PATH') ?: __DIR__ . '/../storage/tracer.sqlite';
echo "Database initialized at {$dbPath}\n";
