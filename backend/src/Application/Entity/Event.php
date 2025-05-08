<?php

declare(strict_types=1);

namespace App\Application\Entity;

use Carbon\Carbon;

class Event
{
    /**
     * @param string|null $id
     * @param string $title
     * @param string $description
     * @param Carbon $startDate
     * @param Carbon $endDate
     */
    public function __construct(
        public readonly ?string $id,
        public readonly string $title,
        public readonly string $description,
        public readonly Carbon $startDate,
        public readonly Carbon $endDate
    ) {
    }

    /**
     * @param array $data
     * @return self
     */
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

    /**
     * @return array
     */
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