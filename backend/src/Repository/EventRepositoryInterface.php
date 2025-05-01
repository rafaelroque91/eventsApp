<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\DTO\QueryParamsDTO;
use App\Domain\Entity\Event;

interface EventRepositoryInterface
{
    public function findAll(QueryParamsDTO $params): array;
    public function findById(string $id): ?Event;
    public function save(Event $event): Event;
}