<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTO\QueryParamsDTO;
use App\Infrastructure\Http\ApiClient;
use App\Domain\Entity\Event;
use App\Infrastructure\Http\ApiException;
use App\Repository\EventRepositoryInterface;

class ApiEventRepository implements EventRepositoryInterface
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function findAll(QueryParamsDTO $params): array
    {
        try {
            return $this->apiClient->get($this->buildUrlWithParams('/api/Events', $params), $params);
        } catch (ApiException $e) {
            error_log("Failed to fetch events: " . $e->getMessage());
            throw new ApiException("An unexpected error occurred while fetching events.", 0, $e);
        }
    }

    private function buildUrlWithParams(string $url, QueryParamsDTO $params) : string
    {
        $query = http_build_query([
            '$top' => API_PAGE_SIZE,
            '$skip' => ($params->page - 1) * API_PAGE_SIZE,
            '$filter' => $params->filter,
            '$orderBy' => $params->orderBy,
        ]);

        return $url . '?' . $query;
    }

    public function findById(string $id): ?Event
    {
        try {
            $data = $this->apiClient->get('/api/Events/' . $id);

            return Event::fromArray($data);
        } catch (ApiException $e) {
            // this api doesn't return 404 error. so we are assuming 400 error is the 404 one.
            if ($e->getCode() === 400) {
                return null;
            }
            error_log("Error finding event by id:{$id} " . $e->getMessage());
            throw new ApiException("An unexpected error occurred while getting the event.", 0, $e);
        }
    }

    public function save(Event $event): Event
    {
        try {
            $payload = $event->toApiPayload();
            $responseData = $this->apiClient->post('/api/Events', $payload);

            return Event::fromArray($responseData);
        } catch (ApiException $e) {
            error_log("Error saving event: " . $e->getMessage());
            throw new ApiException("An unexpected error occurred while saving the event.", 0, $e);
        }
    }
}