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
        // Basic validation could go here, or in a factory/builder
//        if (empty(trim($title))) {
//            throw new \InvalidArgumentException("Event title cannot be empty.");
//        }
//
//        if ($startDate == null || $endDate == null)  {
//            throw new \InvalidArgumentException("End Date cannot be empty.");
//        }
//        if ($endDate < $startDate) {
//            throw new \InvalidArgumentException("End date cannot be before start date.");
//        }
    }

    // Static factory method for creating from API data array
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

    // Method to convert entity to array suitable for API POST/PUT
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