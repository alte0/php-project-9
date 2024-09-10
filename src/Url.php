<?php

namespace App;

use Carbon\Carbon;

final class Url
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $create_at = null;

    public static function fromArray(array $urlData): self
    {
        [$name, $create_at] = $urlData;

        $url = new self();
        $url->setName($name);
        $url->setCreateAt($create_at);

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
        return Carbon::parse($this->create_at)->format('Y-m-d H:i:s');
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setCreateAt(?string $create_at): void
    {
        $this->create_at = $create_at;
    }

    public function exists(): bool
    {
        return !is_null($this->getId());
    }
}
