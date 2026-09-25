<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Campaign
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public string $source,
        public ?string $medium,
        public bool $active,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['name'],
            (string) $row['slug'],
            (string) $row['source'],
            isset($row['medium']) ? (string) $row['medium'] : null,
            (bool) $row['active'],
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'source' => $this->source,
            'medium' => $this->medium,
            'active' => $this->active,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}

