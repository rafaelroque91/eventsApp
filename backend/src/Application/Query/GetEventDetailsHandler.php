<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\Entity\Event;
use App\Repository\EventRepositoryInterface;

class GetEventDetailsHandler
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @throws \App\Infrastructure\Http\ApiException
     */
    public function handle(string $eventId): ?Event
    {
        return $this->eventRepository->findById($eventId);
    }
}