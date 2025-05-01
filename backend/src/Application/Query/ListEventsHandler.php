<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Application\DTO\QueryParamsDTO;
use App\Repository\EventRepositoryInterface;

class ListEventsHandler
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository)
    {
    }

    public function handle(QueryParamsDTO $params): array
    {
        return $this->eventRepository->findAll($params);
    }
}