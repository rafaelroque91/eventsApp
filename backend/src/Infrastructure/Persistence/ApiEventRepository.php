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
    /**
     * @var ApiClient
     */
    private ApiClient $apiClient;

    /**
     * @param ApiClient $apiClient
     */
    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * get events from api
     * @param QueryParamsDTO $params
     * @return array
     */
    public function findAll(QueryParamsDTO $params): array
    {
        try {
            return $this->apiClient->get($this->buildUrlWithParams('/api/Events', $params));
        } catch (ApiException $e) {
            error_log("Failed to fetch events: " . $e->getMessage());
            throw new ApiException("An unexpected error occurred while fetching events.", 0, $e);
        }
    }

    /**
     * build urls params to send to CivicPlus API
     * @param string $url
     * @param QueryParamsDTO $params
     * @return string
     */
    private function buildUrlWithParams(string $url, QueryParamsDTO $params) : string
    {
        $filters = $this->buildODataFilter($params->filters);
        $query = http_build_query([
            '$top' => API_PAGE_SIZE,
            '$skip' => ($params->page - 1) * API_PAGE_SIZE,
            '$filter' => $filters,
            '$orderBy' => $params->orderBy,
        ]);

        return $url . '?' . $query;
    }

    /**
     * generate the query params using odata format (CivicPlus API)
     * @param array|null $filters
     * @return string|null
     */
    private function buildODataFilter(?array $filters): ?string
    {
        if ($filters == null) {
            return null;
        }
        $conditions = [];

        foreach ($filters as $field => $value) {
            if ($value === '' || $value === null) {
                continue;
            }

            if ($field === 'startDate') {
                $conditions[] = "{$field} ge {$value}";
            } elseif ($field === 'endDate') {
                $conditions[] = "{$field} le {$value}";
            } else {
                $escaped = str_replace("'", "''", $value);
                $conditions[] = "{$field} eq '{$escaped}'";
            }
        }

        return implode(' and ', $conditions);
    }

    /**
     * find event by id
     * @param string $id
     * @return Event|null
     */
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

    /**
     * save event
     * @param Event $event
     * @return Event
     */
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