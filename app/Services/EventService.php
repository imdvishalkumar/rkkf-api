<?php

namespace App\Services;

use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class EventService
{
    protected $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function getAllEvents(array $filters = [])
    {
        return $this->eventRepository->all($filters);
    }

    public function getEventById(int $id)
    {
        $event = $this->eventRepository->find($id);
        
        if (!$event) {
            throw new Exception('Event not found', 404);
        }

        return $event;
    }

    public function getPublishedEvents(array $filters = [])
    {
        return $this->eventRepository->getPublished($filters);
    }

    public function createEvent(array $data): array
    {
        DB::beginTransaction();
        
        try {
            $event = $this->eventRepository->create($data);

            DB::commit();

            return [
                'event' => $event,
                'message' => 'Event created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating event: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateEvent(int $id, array $data): array
    {
        $event = $this->eventRepository->find($id);
        
        if (!$event) {
            throw new Exception('Event not found', 404);
        }

        $updated = $this->eventRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update event', 500);
        }

        return [
            'event' => $this->eventRepository->find($id),
            'message' => 'Event updated successfully'
        ];
    }

    public function deleteEvent(int $id): bool
    {
        $event = $this->eventRepository->find($id);
        
        if (!$event) {
            throw new Exception('Event not found', 404);
        }

        return $this->eventRepository->delete($id);
    }

    public function publishEvent(int $id): bool
    {
        return $this->updateEvent($id, ['isPublished' => 1]);
    }

    public function unpublishEvent(int $id): bool
    {
        return $this->updateEvent($id, ['isPublished' => 0]);
    }
}


