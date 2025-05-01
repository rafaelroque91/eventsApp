<?php

declare(strict_types=1);

namespace App\Application\DTO;

use DateTimeImmutable;

class EventDTO implements RequestDTOInterface
{
    public function __construct(
        public string $title,
        public ?string $description,
        public ?DateTimeImmutable $startDate,
        public ?DateTimeImmutable $endDate
    ) {}
    
    public static function createFromRequestData(array $data): self
    {
        return new self(
            $data['title'],
            $data['description'] ?? null, // Use null coalescing for optional fields
            $data['startDate'] ?? null,
            $data['endDate'] ?? null
        );
    }
}