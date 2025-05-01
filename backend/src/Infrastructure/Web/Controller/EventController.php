<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Application\Command\AddEventHandler;
use App\Application\DTO\EventDTO;
use App\Application\Query\GetEventDetailsHandler;
use App\Application\Query\ListEventsHandler;
use App\Application\Validation\RequestUtilTrait;
use App\Domain\Entity\Event;
use App\Infrastructure\Http\ApiException;
use App\Infrastructure\Web\Resource\EventResource;

class EventController
{
    use RequestUtilTrait;

    public function __construct(
        private readonly ListEventsHandler $listEventsHandler,
        private readonly GetEventDetailsHandler $getEventDetailsHandler,
        private readonly AddEventHandler $addEventHandler
    ) {
    }

    public function list(): void
    {
        try {
            $params = $this->getQueryParams($_GET);

            $events = $this->listEventsHandler->handle($params);

            $responseData = EventResource::collection($events,$params);

            //todo criar um resource para padronizar os erros
            $this->jsonResponse($responseData);

        } catch (ApiException $e) {
            var_dump('exception1',$e->getMessage()  . $e->getTraceAsString());
            exit;
            // Log o erro se necessário
            error_log("API Error fetching events: " . $e->getMessage());
            $this->jsonErrorResponse($e, "An unexpected error occurred while fetching events");
        } catch (\Throwable $e) {
            var_dump('exception2',$e->getMessage() . $e->getTraceAsString());
            exit;
            // Log o erro se necessário
            error_log("API Error fetching events: " . $e->getMessage());
            $this->jsonErrorResponse($e, "An unexpected error occurred while fetching events");
}
    }

    public function store(): string
    {
        try {
            $eventDto = $this->validate([
                'title' => ['required','string'],
                'description' => ['optional','string'],
                'startDate' => ['optional','date'],
                'endDate' => ['optional','date']
            ], EventDTO::class);

            $newEvent = $this->addEventHandler->handle(
                $eventDto
            );

            $responseData = EventResource::toArray($newEvent);

            //todo criar um resource para padronizar os erros
            return $this->jsonResponseCreated($responseData);
        } catch (\InvalidArgumentException $e) {
//            var_dump('aaaa', $e->getMessage() . '-' . $e->getTraceAsString() );
//            exit;
            return $this->jsonErrorResponse($e, null,422);
        } catch (ApiException $e) {
            return $this->jsonErrorResponse($e, "Failed to save event: ");
        } catch (\Throwable $e) {
            //todo add a debug flag on config. if true, show the trace.
            return $this->jsonErrorResponse($e, "Failed to save event: ");
            //check the APP_DEBUG  flag created
        }
    }

    public function show(string $id): string
    {
        try {
            $event = $this->getEventDetailsHandler->handle($id);

            if (!$event instanceof Event){
                return $this->jsonResponseNotFound('Event not found');
             }

            $eventData = EventResource::toArray($event);
            return $this->jsonResponse($eventData);

        } catch (ApiException $e) {
            $this->jsonErrorResponse($e, "Failed to fetch events");
        } catch (\Throwable $e) {
            var_dump($e->getMessage()  . '-' . $e->getTraceAsString());

            $this->jsonErrorResponse($e, "Failed to fetch events");
        }
    }
}