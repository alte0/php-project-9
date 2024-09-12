<?php

namespace App;

final class UrlRepository
{
    private \PDO $conn;

    public function __construct(\PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getEntities(): array
    {
        $urls = [];
        $sql = "SELECT * FROM urls ORDER BY id DESC";
        $stmt = $this->conn->query($sql);

        if ($stmt !== false) {
            while ($row = $stmt->fetch()) {
                $url = Url::fromArray([$row['name'], $row['create_at'], null, null]);
                $url->setId($row['id']);
                $urls[] = $url;
            }
        }

        return $urls;
    }

    public function getEntitiesWithLastCheck(): array
    {
        $urls = [];
        $sql = 'SELECT urls.*, url_checks.* FROM urls 
                LEFT JOIN (
                    SELECT url_id, MAX(created_at) AS last_check_at, status_code FROM url_checks 
                        where status_code is not null 
                    GROUP BY url_id, status_code
                ) as url_checks 
                    ON urls.id = url_checks.url_id
                ORDER BY id desc';
        $stmt = $this->conn->query($sql);

        if ($stmt !== false) {
            while ($row = $stmt->fetch()) {
                $url = Url::fromArray([$row['name'], $row['created_at'], $row['last_check_at'], $row['status_code']]);
                $url->setId($row['id']);
                $urls[] = $url;
            }
        }

        return $urls;
    }

    public function find(int $id): ?Url
    {
        $sql = 'SELECT * FROM urls WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
            $url = Url::fromArray([$row['name'], $row['create_at'], null, null]);
            $url->setId($row['id']);

            return $url;
        }

        return null;
    }

    public function findByName(string $name): ?Url
    {
        $sql = 'SELECT * FROM urls WHERE name LIKE ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$name]);

        if ($row = $stmt->fetch()) {
            $url = Url::fromArray([$row['name'], $row['create_at'], null, null]);
            $url->setId($row['id']);

            return $url;
        }

        return null;
    }

    public function save(Url $url): void
    {
        if ($url->exists()) {
            $this->update($url);
        } else {
            $this->create($url);
        }
    }

    private function update(Url $url): void
    {
        $sql = 'UPDATE urls SET name = :name, create_at = :create_at WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $id = $url->getId();
        $name = $url->getName();
        $create_at = $url->getCreateAt();
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':create_at', $create_at);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    private function create(Url $url): void
    {
        $sql = 'INSERT INTO urls (name, create_at) VALUES (:name, :create_at)';
        $stmt = $this->conn->prepare($sql);
        $name = $url->getName();
        $create_at = $url->getCreateAt();
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':create_at', $create_at);
        $stmt->execute();
        $id = (int)$this->conn->lastInsertId();
        $url->setId($id);
    }
}
