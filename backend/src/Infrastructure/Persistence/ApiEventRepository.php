<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

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

    public function findAll(): array
    {
        try {
            return $this->apiClient->get(API_ENDPOINT_EVENTS);
        } catch (ApiException $e) {
            error_log("Failed to fetch all events: " . $e->getMessage());
            // Re-throw or handle as needed (e.g., return empty array, depending on requirements)
            throw $e;
        }
    }

    public function findById(string $id): ?Event
    {
        try {
            $data = $this->apiClient->get(API_ENDPOINT_EVENT_DETAIL_PREFIX . $id);

            return Event::fromArray($data);
        } catch (ApiException $e) {
            // this api doesn't return 404 error. so we are assuming 400 error is the 404 one.
            if ($e->getCode() === 400) {
                return null;
            }
            throw $e; // Re-throw other errors
        }
//        } catch (\Throwable $e) {
//            error_log("Error to get the event ID {$id}. Error:" . $e->getMessage());
//            throw new ApiException("Error to get the event ID {$id}. Error:" $e->getMessage() 500, $e);
//        }
    }

    public function save(Event $event): Event
    {
        try {
            $payload = $event->toApiPayload();

            $responseData = $this->apiClient->post(API_ENDPOINT_EVENTS, $payload);
//            if (!isset($responseData['id'])) {
//                error_log("API did not return ID after creating event. Payload: " . json_encode($payload) . " Response: " . json_encode($responseData));
//                throw new ApiException("API did not return an ID for the newly created event.");
//            }
            return Event::fromArray($responseData);
        } catch (ApiException $e) {
            error_log("Failed to save event: " . $e->getMessage() . " Payload: " . json_encode($payload ?? []));
            throw $e;
        } catch (\Throwable $e) {
            var_dump('esse',$e->getMessage());
            exit;
            error_log("Error saving event: " . $e->getMessage());
            throw new ApiException("An unexpected error occurred while saving the event.", 0, $e);
        }
    }
}