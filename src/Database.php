<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function connect(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $config = require __DIR__ . '/../config/database.php';

        try {
            self::$connection = new PDO(
                'sqlite:' . $config['database'],
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            self::createTables();

            return self::$connection;
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    private static function createTables(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            keywords TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS publications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER NOT NULL,
            citation_key TEXT,
            type TEXT,
            title TEXT,
            authors TEXT,
            year TEXT,
            journal TEXT,
            volume TEXT,
            number TEXT,
            pages TEXT,
            publisher TEXT,
            doi TEXT,
            url TEXT,
            abstract TEXT,
            keywords TEXT,
            is_selected INTEGER DEFAULT 0,
            source_file TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
        );

        CREATE INDEX IF NOT EXISTS idx_project_id ON publications(project_id);
        CREATE INDEX IF NOT EXISTS idx_is_selected ON publications(is_selected);
        ";

        self::$connection->exec($sql);
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $db = self::connect();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function lastInsertId(): string
    {
        return self::connect()->lastInsertId();
    }
}
