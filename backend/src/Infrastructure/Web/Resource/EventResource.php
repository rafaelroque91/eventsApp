<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Resource;

use App\Domain\Entity\Event;

class EventResource
{
    public static function toArray($event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'startDate' => $event->startDate?->format('Y-m-d'),
            'endDate' => $event->endDate?->format('Y-m-d'),
        ];
    }
    public static function collection(array $events): array
    {
        return array_map(function ($item) {
            var_dump($item);

            exit;
            }, $events["items"]);
    }
}