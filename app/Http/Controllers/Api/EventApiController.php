<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EventService;
use App\Services\StudentService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class EventApiController extends Controller
{
    protected $eventService;
    protected $studentService;

    public function __construct(
        EventService $eventService,
        StudentService $studentService
    ) {
        $this->eventService = $eventService;
        $this->studentService = $studentService;
    }

    /**
     * List all events
     * GET /api/events?category=Tournaments
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
                'upcoming_event' => 'nullable|array',
                'upcoming_event.*' => 'string',
                // category may be passed as a single string or an array of names
                'category' => 'nullable',
            ]);

            $perPage = (int) $request->input('per_page', 15);
            $page = (int) $request->input('page', 1);

            // upcoming_event: array of strings; if contains 'All' -> all upcoming
            $upcomingEvent = $request->input('upcoming_event', null);

            // category: accept string or array. Normalize to array of names or null.
            $categoryInput = $request->input('category', null);
            $categoryNames = null;
            if (!is_null($categoryInput) && $categoryInput !== '') {
                if (is_array($categoryInput)) {
                    $categoryNames = array_values(array_filter(array_map('trim', $categoryInput), function ($v) {
                        return $v !== '';
                    }));
                } else {
                    // support comma-separated string like "Tournaments,Workshops" or single value
                    $categoryNames = array_values(array_filter(array_map('trim', explode(',', (string) $categoryInput)), function ($v) {
                        return $v !== '';
                    }));
                }

                if (empty($categoryNames)) {
                    $categoryNames = null;
                }
            }

            $events = $this->eventService->getAllEvents($perPage, $categoryNames, $upcomingEvent);
            $categories = DB::table('categories')
                ->where('active', 1)
                ->pluck('name')
                ->toArray();
            // Map each paginator item through EventResource to sanitize fields
            $items = array_map(function ($ev) use ($request) {
                return (new \App\Http\Resources\EventResource($ev))->toArray($request);
            }, $events->items());

            // Build grouped response under data.upcoming_event with pagination meta
            $responseData = [
                'category' => $categories,
                'upcoming_event' => $items,
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                    'last_page' => $events->lastPage(),
                    'from' => $events->firstItem(),
                    'to' => $events->lastItem(),
                ],
            ];

            return ApiResponseHelper::success($responseData, 'Events retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Create Event
     * POST /api/events
     */
    public function store(\App\Http\Requests\StoreEventRequest $request)
    {
        try {
            $event = $this->eventService->createEvent($request->validated());
            return ApiResponseHelper::success(new \App\Http\Resources\EventResource($event), 'Event created successfully', 201);
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get Event by ID
     * GET /api/events/{id}
     */
    public function show($id)
    {
        try {
            $event = $this->eventService->getEventById($id);
            if (!$event) {
                return ApiResponseHelper::error('Event not found', 404);
            }
            return ApiResponseHelper::success(new \App\Http\Resources\EventResource($event), 'Event retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Update Event
     * PUT /api/events/{id}
     */
    public function update(\App\Http\Requests\UpdateEventRequest $request, $id)
    {
        try {
            $event = $this->eventService->updateEvent($id, $request->validated());
            if (!$event) {
                return ApiResponseHelper::error('Event not found', 404);
            }
            return ApiResponseHelper::success(new \App\Http\Resources\EventResource($event), 'Event updated successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Delete Event
     * DELETE /api/events/{id}
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->eventService->deleteEvent($id);
            if (!$deleted) {
                return ApiResponseHelper::error('Event not found or could not be deleted', 404);
            }
            return ApiResponseHelper::success(null, 'Event deleted successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get Upcoming Events
     * GET /api/events/upcoming
     */
    public function upcoming()
    {
        try {
            $events = $this->eventService->getUpcomingEvents();
            return ApiResponseHelper::success(
                \App\Http\Resources\EventResource::collection($events),
                'Upcoming events retrieved successfully'
            );
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get eligible students for event
     * GET /api/event/get-eligible-students?event_id=1
     */
    public function getEligibleStudents(Request $request)
    {
        try {
            $request->validate([
                'event_id' => 'required|integer|exists:event,event_id',
            ]);

            $eventId = $request->input('event_id');

            // Get students who have paid event fees for this event
            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('event_fees as ef', 's.student_id', '=', 'ef.student_id')
                ->where('s.active', 1)
                ->where('ef.event_id', $eventId)
                ->where('ef.status', 1)
                ->select('s.*', 'br.name as branch_name')
                ->distinct()
                ->get();

            return ApiResponseHelper::success($students, 'Eligible students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get event applied students
     * GET /api/event/get-applied?branch_id=1&param=true
     */
    public function getEventApplied(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
                'param' => 'nullable|string',
            ]);

            $branchId = $request->input('branch_id');
            $param = $request->input('param');

            $query = DB::table('event_fees as ef')
                ->join('students as s', 'ef.student_id', '=', 's.student_id')
                ->join('event as e', 'ef.event_id', '=', 'e.event_id')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.branch_id', $branchId)
                ->where('ef.status', 1);

            if ($param === 'true') {
                // Additional filtering if needed
            }

            $applied = $query->select(
                'ef.*',
                's.student_id as grno',
                DB::raw('CONCAT(s.firstname, " ", s.lastname) as student_name'),
                'e.name as event_name',
                'br.name as branch_name'
            )
                ->orderBy('ef.date', 'desc')
                ->get();

            return ApiResponseHelper::success($applied, 'Event applied students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
}
