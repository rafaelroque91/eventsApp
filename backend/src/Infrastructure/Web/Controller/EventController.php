<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Application\Command\AddEventHandler;
use App\Application\DTO\EventDTO;
use App\Application\Query\GetEventDetailsHandler;
use App\Application\Query\ListEventsHandler;
use App\Application\Validation\RequestUtilTrait;
use App\Domain\Entity\Event;
use App\Infrastructure\Web\Resource\EventResource;
use Throwable;

class EventController
{
    use RequestUtilTrait;

    /**
     * @param ListEventsHandler $listEventsHandler
     * @param GetEventDetailsHandler $getEventDetailsHandler
     * @param AddEventHandler $addEventHandler
     */
    public function __construct(
        private readonly ListEventsHandler $listEventsHandler,
        private readonly GetEventDetailsHandler $getEventDetailsHandler,
        private readonly AddEventHandler $addEventHandler
    ) {
    }

    /**
     * list Events method
     * @return string
     */
    public function list(): string
    {
        try {
            $params = $this->getQueryParams($_GET);

            $events = $this->listEventsHandler->handle($params);

            $responseData = EventResource::collection($events,$params);

            return $this->jsonResponse($responseData);
        } catch (Throwable $e) {
            return $this->jsonErrorResponse($e, "An unexpected error occurred while fetching events");
        }
    }

    /**
     * store Event method
     * @return string
     */
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

            return $this->jsonResponseCreated($responseData);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonErrorResponse($e, null,422);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse($e, "Failed to save event: ");
        }
    }

    /**
     * show an event
     * @param string $id
     * @return string
     */
    public function show(string $id): string
    {
        try {
            $event = $this->getEventDetailsHandler->handle($id);

            if (!$event instanceof Event){
                return $this->jsonResponseNotFound('Event not found');
             }

            $eventData = EventResource::toArray($event);
            return $this->jsonResponse($eventData);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse($e, "Failed to fetch events");
        }
    }
}