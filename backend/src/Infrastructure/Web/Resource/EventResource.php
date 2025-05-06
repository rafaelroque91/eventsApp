<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Resource;

use App\Application\DTO\QueryParamsDTO;
use Carbon\Carbon;

class EventResource
{
    /**
     * format the event object response
     * @param $event
     * @return array
     */
    public static function toArray($event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'startDate' => self::formatDate($event->startDate),
            'endDate' => self::formatDate($event->endDate),
        ];
    }

    /**
     * format the event list response
     * @param array $data
     * @param QueryParamsDTO $params
     * @return array
     */
    public static function collection(array $data, QueryParamsDTO $params) : array
    {
        $total = $data['total'] ?? 0;
        $items = $data['items'] ?? [];

        $data = array_map(function ($item) {
            return self::toArray((object) $item);
        },$items);


        return [
            'data' => $data,
            "meta" => [
                "page" => [
                    "current_page" => $params->page,
                    "per_page" => API_PAGE_SIZE,
                    "from" => $params->page * API_PAGE_SIZE - API_PAGE_SIZE + 1,
                    "to" => min($params->page * API_PAGE_SIZE, $total),
                    "total" => $total,
                    "last_page" => ceil($total / API_PAGE_SIZE)
                ]
            ]
        ];
    }

    /**
     * format date to Y-m-d
     * @param mixed $date
     * @return string|null
     */
    private static function formatDate(mixed $date) : ?string
    {
        if ($date == null) {
            return null;
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::make($date);
        }
        return $date->format('Y-m-d');
    }
}