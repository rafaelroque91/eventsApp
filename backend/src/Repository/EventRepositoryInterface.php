<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Entity\Event;

interface EventRepositoryInterface
{
    public function findAll(): array;
    public function findById(string $id): ?Event;
    public function save(Event $event): Event;
}