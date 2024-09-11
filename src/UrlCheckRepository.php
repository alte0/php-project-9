<?php

namespace App;

final class UrlCheckRepository
{
    private \PDO $conn;

    public function __construct(\PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getEntities(): array
    {
        $urls = [];
        $sql = 'SELECT * FROM url_checks ORDER BY id DESC';
        $stmt = $this->conn->query($sql);

        while ($row = $stmt->fetch()) {
            $url = UrlCheck::fromArray([$row['name'], $row['create_at']]);
            $url->setId($row['id']);
            $urls[] = $url;
        }

        return $urls;
    }

    public function find(int $id): ?UrlCheck
    {
        $sql = 'SELECT * FROM url_checks WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
            $url = UrlCheck::fromArray([$row['name'], $row['create_at']]);
            $url->setId($row['id']);

            return $url;
        }

        return null;
    }

    /**
     * @param int $urlId
     * @return UrlCheck[]
     */
    public function findByUrlId(int $urlId): array
    {
        $arr = [];
        $sql = 'SELECT * FROM url_checks WHERE url_id = ? ORDER BY id DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$urlId]);

        while ($row = $stmt->fetch()) {
            $urlCheck = UrlCheck::fromArray([
                $row['url_id'], $row['status_code'], $row['h1'], $row['title'], $row['created_at'], $row['description']
            ]);
            $urlCheck->setId($row['id']);
            $arr[] = $urlCheck;
        }

        return $arr;
    }

    public function save(UrlCheck $urlCheck): void
    {
        if ($urlCheck->exists()) {
            $this->update($urlCheck);
        } else {
            $this->create($urlCheck);
        }
    }

    private function update(UrlCheck $urlCheck): void
    {
        $sql = 'UPDATE url_checks 
                SET status_code = :status_code, h1 = :h1, title = :title, description = :description, 
                    create_at = :create_at 
                WHERE id = :id';
        $stmt = $this->conn->prepare($sql);

        $id = $urlCheck->getId();
        $statusCode = $urlCheck->getStatusCode();
        $h1 = $urlCheck->getH1();
        $title = $urlCheck->getTitle();
        $description = $urlCheck->getDescription();
        $createAt = $urlCheck->getCreatedAt();

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status_code', $statusCode);
        $stmt->bindParam(':h1', $h1);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':create_at', $createAt);

        $stmt->execute();
    }

    public function create(UrlCheck $urlCheck): void
    {
        $sql = 'INSERT INTO url_checks 
                    (url_id, status_code, h1, title, description, created_at)
                VALUES 
                    (:url_id, :status_code, :h1, :title, :description, :created_at)';
        $stmt = $this->conn->prepare($sql);

        $urlId = $urlCheck->getUrlId();
        $statusCode = $urlCheck->getStatusCode();
        $h1 = $urlCheck->getH1();
        $title = $urlCheck->getTitle();
        $description = $urlCheck->getDescription();
        $createdAt = $urlCheck->getCreatedAt();

        $stmt->bindParam(':url_id', $urlId);
        $stmt->bindParam(':status_code', $statusCode);
        $stmt->bindParam(':h1', $h1);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':created_at', $createdAt);
        $stmt->execute();

        $id = (int)$this->conn->lastInsertId();
        $urlCheck->setId($id);
    }
}
