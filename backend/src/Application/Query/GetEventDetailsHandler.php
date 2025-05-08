<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Application\Entity\Event;
use App\Repository\EventRepositoryInterface;

class GetEventDetailsHandler
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository)
    {}

    public function handle(string $eventId): ?Event
    {
        return $this->eventRepository->findById($eventId);
    }
}