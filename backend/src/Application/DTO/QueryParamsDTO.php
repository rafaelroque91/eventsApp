<?php

declare(strict_types=1);

namespace App\Application\DTO;

class QueryParamsDTO
{
    /**
     * @param int|null $page
     * @param array|null $filters
     * @param string|null $orderBy
     */
    public function __construct(
        public ?int $page,
        public ?array $filters,
        public ?string $orderBy,
    ) {}

    /**
     * @param array $data
     * @return self
     */
    public static function createFromRequest(array $data): self
    {
        return new self(
            isset($data['page']) ? (int)$data['page'] : 1,
            $data['filter'] ?? null,
            $data['orderBy'] ?? null
        );
    }
}