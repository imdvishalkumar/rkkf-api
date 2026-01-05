<?php

namespace App\Services;

use App\Repositories\EventRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class EventService
{
    protected $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function getAllEvents(int $perPage = 15, ?string $category = null)
    {
        return $this->eventRepository->getAll($perPage, $category);
    }

    public function getEventById(int $id)
    {
        return $this->eventRepository->find($id);
    }

    public function createEvent(array $data)
    {
        DB::beginTransaction();

        try {
            // Map API fields to DB fields if needed (handled by Accessors/Mutators ideally, but explicit mapping here for safety)
            $dbData = $this->mapToDb($data);

            $event = $this->eventRepository->create($dbData);

            // Notification Logic (Ported from legacy event.php)
            // Legacy: select student_id from students; -> insert into notification ...
            // Optimizing to chunking to avoid memory issues if student count is huge

            $this->sendEventNotifications($event);

            DB::commit();
            return $event;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateEvent(int $id, array $data)
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return null;
        }

        DB::beginTransaction();
        try {
            $dbData = $this->mapToDb($data);
            $this->eventRepository->update($event, $dbData);

            DB::commit();
            return $event;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteEvent(int $id)
    {
        $event = $this->eventRepository->find($id);
        if (!$event) {
            return false;
        }

        return $this->eventRepository->delete($event);
    }

    public function getUpcomingEvents()
    {
        return $this->eventRepository->getUpcoming();
    }

    protected function mapToDb(array $data)
    {
        // If API sends snake_case keys that match our accessors, Model will handle setAttribute.
        // However, standard fillable expects db column names usually unless we strictly use $model->fill().
        // Let's ensure we have the correct DB keys from the API keys.

        $mapped = $data;

        if (isset($data['title'])) {
            $mapped['name'] = $data['title'];
            unset($mapped['title']);
        }
        if (isset($data['event_start_datetime'])) {
            $mapped['from_date'] = $data['event_start_datetime'];
            unset($mapped['event_start_datetime']);
        }
        if (isset($data['event_end_datetime'])) {
            $mapped['to_date'] = $data['event_end_datetime'];
            unset($mapped['event_end_datetime']);
        }

        // Default legacy required fields if missing in API
        if (!isset($mapped['type']))
            $mapped['type'] = 'General';
        if (!isset($mapped['fees']))
            $mapped['fees'] = 0;
        if (!isset($mapped['fees_due_date']))
            $mapped['fees_due_date'] = $mapped['from_date'] ?? date('Y-m-d');
        if (!isset($mapped['penalty']))
            $mapped['penalty'] = 0;
        if (!isset($mapped['penalty_due_date']))
            $mapped['penalty_due_date'] = $mapped['from_date'] ?? date('Y-m-d');

        return $mapped;
    }

    protected function sendEventNotifications($event)
    {
        // Replicating legacy logic:
        // $q = "insert into notification (title,details,student_id,viewed,type,sent,timestamp) VALUES ...";

        $title = 'Event';
        $details = $event->name . ' is on ' . $event->from_date->format('Y-m-d');
        $timestamp = now();

        // Using direct DB insert for performance to match legacy "one-shot" feel, 
        // but cleaner with chunking.

        DB::table('students')->select('student_id')->orderBy('student_id')->chunk(500, function ($students) use ($title, $details, $timestamp) {
            $notifications = [];
            foreach ($students as $student) {
                $notifications[] = [
                    'title' => $title,
                    'details' => $details,
                    'student_id' => $student->student_id,
                    'viewed' => 0,
                    'type' => 'exam', // Legacy used 'exam' for events... keeping it.
                    'sent' => 0,
                    'timestamp' => $timestamp,
                ];
            }
            DB::table('notification')->insert($notifications);
        });
    }
}
