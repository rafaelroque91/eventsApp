<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\EventDTO;
use App\Domain\Entity\Event;
use App\Repository\EventRepositoryInterface;
use DateTimeImmutable;

// Optional: Define a Command object/DTO for better structure
// class AddEventCommand { public function __construct(public string $title, ...) {} }

class AddEventHandler
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function handle(
        EventDTO $eventDto,
    ): Event {

        if ($eventDto->endDate < $eventDto->startDate) {
            throw new \InvalidArgumentException("End date cannot be before start date.");
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