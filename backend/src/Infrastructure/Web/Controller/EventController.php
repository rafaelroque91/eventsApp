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
//        $this->listEventsHandler = $listEventsHandler;
//        $this->getEventDetailsHandler = $getEventDetailsHandler;
//        $this->addEventHandler = $addEventHandler;
    }

    // Renders the main page which will likely contain the Vue app
//    public function index(): void
//    {
//        // This page will load Vue, which will then call listApi()
//        $this->render('events/index');
//    }

    public function listApi(): void
    {
        try {

//exemplo de filtro
//            https://interview.civicplus.com/cc949ee3-beb0-4573-9878-2fa5af34bad2/api/Events?$top=2&$skip=2&$filter=title eq 'test 122'&$orderBy=title
            $events = $this->listEventsHandler->handle(); // Assuming this returns array<Event>
//            var_dump($events);
//            exit;

            // Use the resource to transform the collection
            $eventData = EventResource::collection($events);

//            $this->jsonResponse($eventData);
//
//            $events = $this->listEventsHandler->handle();
//            // Convert events to a simple array format suitable for JSON
//            $eventData = array_map(fn($event) => [
//                'id' => $event->id,
//                'eventTitle' => $event->eventTitle,
//                'description' => $event->description, // Keep description short for list view?
//                'startDate' => $event->startDate->format(\DateTimeInterface::ISO8601), // Consistent format
//                'endDate' => $event->endDate->format(\DateTimeInterface::ISO8601),
//            ], $events);
            $this->jsonResponse($eventData);
        } catch (ApiException $e) {
            $this->jsonErrorResponse("Failed to fetch events: " . $e->getMessage(), $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            var_dump($e->getMessage()  . '-' . $e->getTraceAsString());
            $this->jsonErrorResponse("An unexpected error occurred: " . $e->getMessage());
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
            return $this->jsonErrorResponse("Failed to save event: " . $e->getMessage(), $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            //todo add a debug flag on config. if true, show the trace.
            //check the APP_DEBUG  flag created
            return $this->jsonErrorResponse("An unexpected error occurred2: " . $e->getMessage() . '-' . $e->getTraceAsString(), 500);
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
            $this->jsonErrorResponse("Failed to fetch events: " . $e->getMessage(), $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            var_dump($e->getMessage()  . '-' . $e->getTraceAsString());
            $this->jsonErrorResponse("An unexpected error occurred: " . $e->getMessage());
        }
    }
}