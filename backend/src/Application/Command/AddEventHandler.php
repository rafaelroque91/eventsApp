<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\EventDTO;
use App\Domain\Entity\Event;
use App\Repository\EventRepositoryInterface;

class AddEventHandler
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository)
    {
    }

    public function handle(
        EventDTO $eventDto,
    ): Event {

        if ($eventDto->endDate <= $eventDto->startDate) {
            throw new \InvalidArgumentException("End date should be greater than start date.");
        }

        $newEvent = new Event(
            null,
            $eventDto->title,
            $eventDto->description,
            $eventDto->startDate,
            $eventDto->endDate
        );

        return $this->eventRepository->save($newEvent);
    }
}