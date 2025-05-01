<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;

class Event
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $title,
        public readonly string $description,
        public readonly DateTimeImmutable $startDate,
        public readonly DateTimeImmutable $endDate
    ) {
    }

    public static function fromArray(array $data): self
    {
        try {
            return new self(
                $data['id'] ?? null,
                $data['title'] ?? '',
                $data['description'] ?? '',
                new \DateTimeImmutable($data['startDate'] ?? 'now'),
                new \DateTimeImmutable($data['endDate'] ?? 'now')
            );
        } catch (\Exception $e) {
        var_dump('erro1231',$e->getMessage());
        exit;
            // Log the error and the problematic data
            error_log("Error creating Event from array: " . $e->getMessage() . " Data: " . print_r($data, true));
            // Return a default/empty event or re-throw a domain-specific exception
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