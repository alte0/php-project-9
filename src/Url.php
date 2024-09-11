<?php

namespace App;

use Carbon\Carbon;

final class Url
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $createAt = null;
    private ?string $lastCheckAt = null;
    private ?int $statusCode = null;

    public static function fromArray(array $urlData): self
    {
        [$name, $createAt, $lastCheckAt, $statusCode] = $urlData;

        $url = new self();
        $url->setName($name);
        $url->setCreateAt($createAt);
        $url->setLastCheckAt($lastCheckAt);
        $url->setStatusCode($statusCode);

        return $url;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCreateAt(): ?string
    {
        return $this->createAt;
    }

    public function getCreateAtForHuman(): ?string
    {
        $createAt = $this->getCreateAt();

        if ($createAt !== null) {
            return Carbon::parse($createAt)->format('Y-m-d H:i:s');
        }

        return null;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setCreateAt(?string $createAt): void
    {
        $this->createAt = $createAt;
    }

    public function exists(): bool
    {
        return !is_null($this->getId());
    }

    public function getLastCheckAt(): ?string
    {
        return $this->lastCheckAt;
    }

    public function setLastCheckAt(?string $lastCheckAt): void
    {
        $this->lastCheckAt = $lastCheckAt;
    }

    public function getLastCheckAtForHuman(): ?string
    {
        $createAt = $this->getLastCheckAt();

        if ($createAt !== null) {
            return Carbon::parse($createAt)->format('Y-m-d H:i:s');
        }

        return null;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(?int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }
}
