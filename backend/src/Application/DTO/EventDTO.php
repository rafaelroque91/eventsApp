<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Carbon\Carbon;

class EventDTO implements RequestDTOInterface
{
    public function __construct(
        public string $title,
        public ?string $description,
        public ?Carbon $startDate,
        public ?Carbon $endDate
    ) {}
    
    public static function createFromRequestData(array $data): self
    {
        return new self(
            $data['title'],
            $data['description'] ?? null,
            $data['startDate'] ?? null,
            $data['endDate'] ?? null
        );
    }
}