<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Repository\EventRepositoryInterface;

class ListEventsHandler
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @return \App\Domain\Entity\Event[]
     * @throws \App\Infrastructure\Http\ApiException
     */
    public function handle(): array
    {
        //todo Add pagination
        return $this->eventRepository->findAll();
    }
}