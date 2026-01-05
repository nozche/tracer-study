<?php

class Database
{
    private static ?\PDO $connection = null;

    public static function connection(): \PDO
    {
        if (self::$connection === null) {
            $dbPath = __DIR__ . '/../data/database.sqlite';
            $shouldSeed = !file_exists($dbPath);

            self::$connection = new \PDO('sqlite:' . $dbPath);
            self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            self::migrate();

            if ($shouldSeed) {
                self::seed();
            }
        }

        return self::$connection;
    }

    private static function migrate(): void
    {
        $db = self::$connection;

        $db->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT "user",
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $db->exec(
            'CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                action TEXT NOT NULL,
                metadata TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )'
        );

        $db->exec(
            'CREATE TABLE IF NOT EXISTS login_attempts (
                ip TEXT PRIMARY KEY,
                attempts INTEGER NOT NULL DEFAULT 0,
                last_attempt INTEGER,
                locked_until INTEGER
            )'
        );
    }

    private static function seed(): void
    {
        $db = self::$connection;
        $count = (int) $db->query('SELECT COUNT(*) as total FROM users')->fetchColumn();
        if ($count === 0) {
            $stmt = $db->prepare('INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, :role)');
            $stmt->execute([
                ':username' => 'admin',
                ':password_hash' => password_hash('Password123!', PASSWORD_BCRYPT),
                ':role' => 'admin',
            ]);
        }
    }
}
