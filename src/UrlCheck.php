<?php

namespace App;

use Carbon\Carbon;

final class UrlCheck
{
    private ?int $id = null;
    private ?int $urlId = null;
    private ?int $statusCode = null;
    private ?string $h1 = null;
    private ?string $title = null;
    private ?string $createdAt = null;
    private ?string $description = null;

    /** $urlId, $statusCode, $h1, $title, $createdAt, $description
     * @param array $urlData
     * @return self
     */
    public static function fromArray(array $urlData): self
    {
        [$urlId, $statusCode, $h1, $title, $createdAt, $description] = $urlData;

        $urlCheck = new self();
        $urlCheck->setUrlId($urlId);
        $urlCheck->setStatusCode($statusCode);
        $urlCheck->setH1($h1);
        $urlCheck->setTitle($title);
        $urlCheck->setCreatedAt($createdAt);
        $urlCheck->setDescription($description);

        return $urlCheck;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUrlId(): ?int
    {
        return $this->urlId;
    }

    public function setUrlId(?int $urlId): void
    {
        $this->urlId = $urlId;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(?int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getH1(): ?string
    {
        return $this->h1;
    }

    public function setH1(?string $h1): void
    {
        $this->h1 = $h1;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getCreateAtForHuman(): ?string
    {
        $createdAt = $this->getCreatedAt();

        if ($createdAt !== null) {
            return Carbon::parse($createdAt)->format('Y-m-d H:i:s');
        }

        return null;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function exists(): bool
    {
        return !is_null($this->getId());
    }
}
