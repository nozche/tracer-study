<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $dbPath = getenv('TRACER_DB_PATH') ?: __DIR__ . '/../storage/tracer.sqlite';
            $dsn = 'sqlite:' . $dbPath;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            self::$pdo = new PDO($dsn, null, null, $options);
        }

        return self::$pdo;
    }
}
