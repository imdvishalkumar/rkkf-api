<?php

namespace App\Repositories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

class EventRepository
{
    protected $model;

    public function __construct(Event $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Event
    {
        return $this->model->create($data);
    }

    public function update(Event $event, array $data): bool
    {
        return $event->update($data);
    }

    public function delete(Event $event): bool
    {
        return $event->delete();
    }

    public function find(int $id): ?Event
    {
        return $this->model->withCount('eventComments')->find($id);
    }

    /**
     * Get all events with optional category filter (accepts category id or array of category names)
     * and optional upcoming event type filters.
     *
     * @param int $perPage
     * @param mixed $category null|int|array
     * @param mixed $upcomingEvent null|array
     */
    public function getAll(int $perPage = 15, $category = null, $upcomingEvent = null, ?string $search = null)
    {
        $query = $this->model->with('category')->withCount('eventComments')->orderBy('from_date', 'desc');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($category) {
            if (is_array($category)) {
                // filter by category names via relation
                $query->whereHas('category', function ($q) use ($category) {
                    $q->whereIn('name', $category);
                });
            } else {
                // assume integer id
                $query->where('category_id', (int) $category);
            }
        }

        if ($upcomingEvent && is_array($upcomingEvent)) {
            $filtered = array_values(array_filter($upcomingEvent, function ($v) {
                return $v !== '' && $v !== null;
            }));
            if (!empty($filtered) && !in_array('All', $filtered, true)) {
                // assume upcoming_event contains event types; filter by 'type' column
                $query->whereIn('type', $filtered);
            }
        }

        return $query->paginate($perPage);
    }

    public function getUpcoming()
    {
        return $this->model->upcoming()->get();
    }
}
