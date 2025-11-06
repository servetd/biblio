<?php

namespace App;

class Publication
{
    public static function findByProject(int $projectId, int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM publications WHERE project_id = ? ORDER BY year DESC, authors ASC";

        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = Database::query($sql, [$projectId, $limit, $offset]);
        } else {
            $stmt = Database::query($sql, [$projectId]);
        }

        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::query("SELECT * FROM publications WHERE id = ?", [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function create(int $projectId, array $data): int
    {
        $stmt = Database::query(
            "INSERT INTO publications (
                project_id, citation_key, type, title, authors, year,
                journal, volume, number, pages, publisher, doi, url,
                abstract, keywords, source_file
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $projectId,
                $data['citation_key'] ?? null,
                $data['type'] ?? null,
                $data['title'] ?? null,
                $data['authors'] ?? null,
                $data['year'] ?? null,
                $data['journal'] ?? null,
                $data['volume'] ?? null,
                $data['number'] ?? null,
                $data['pages'] ?? null,
                $data['publisher'] ?? null,
                $data['doi'] ?? null,
                $data['url'] ?? null,
                $data['abstract'] ?? null,
                $data['keywords'] ?? null,
                $data['source_file'] ?? null,
            ]
        );

        return (int) Database::lastInsertId();
    }

    public static function toggleSelected(int $id): bool
    {
        $stmt = Database::query(
            "UPDATE publications SET is_selected = NOT is_selected WHERE id = ?",
            [$id]
        );
        return $stmt->rowCount() > 0;
    }

    public static function getSelected(int $projectId): array
    {
        $stmt = Database::query(
            "SELECT * FROM publications WHERE project_id = ? AND is_selected = 1 ORDER BY year DESC, authors ASC",
            [$projectId]
        );
        return $stmt->fetchAll();
    }

    public static function deleteByProject(int $projectId): bool
    {
        $stmt = Database::query("DELETE FROM publications WHERE project_id = ?", [$projectId]);
        return true;
    }

    public static function countByProject(int $projectId): int
    {
        $stmt = Database::query(
            "SELECT COUNT(*) as count FROM publications WHERE project_id = ?",
            [$projectId]
        );
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
}
