<?php

namespace App;

class Project
{
    public static function all(): array
    {
        $stmt = Database::query("SELECT * FROM projects ORDER BY updated_at DESC");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::query("SELECT * FROM projects WHERE id = ?", [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function create(string $name, string $description = '', string $keywords = ''): int
    {
        Database::query(
            "INSERT INTO projects (name, description, keywords) VALUES (?, ?, ?)",
            [$name, $description, $keywords]
        );
        return (int) Database::lastInsertId();
    }

    public static function update(int $id, string $name, string $description = '', string $keywords = ''): bool
    {
        $stmt = Database::query(
            "UPDATE projects SET name = ?, description = ?, keywords = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$name, $description, $keywords, $id]
        );
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        $stmt = Database::query("DELETE FROM projects WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public static function getPublicationCount(int $projectId): int
    {
        $stmt = Database::query(
            "SELECT COUNT(*) as count FROM publications WHERE project_id = ?",
            [$projectId]
        );
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    public static function getSelectedCount(int $projectId): int
    {
        $stmt = Database::query(
            "SELECT COUNT(*) as count FROM publications WHERE project_id = ? AND is_selected = 1",
            [$projectId]
        );
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    public static function touch(int $id): void
    {
        Database::query("UPDATE projects SET updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
    }
}
