<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Carbon\Carbon;

class Event
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $title,
        public readonly string $description,
        public readonly Carbon $startDate,
        public readonly Carbon $endDate
    ) {
    }

    public static function fromArray(array $data): self
    {
        try {
            return new self(
                $data['id'] ?? null,
                $data['title'] ?? '',
                $data['description'] ?? '',
                Carbon::make($data['startDate'] ?? 'now'),
                Carbon::make($data['endDate'] ?? 'now')
            );
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to create Event entity from API data.", 0, $e);
        }
    }

    public function toApiPayload(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'startDate' => $this->startDate->format('Y-m-d'),
            'endDate' => $this->endDate->format('Y-m-d'),
        ];
    }
}