<?php

namespace App\Repositories;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EventRepository implements EventRepositoryInterface
{
    protected $model;

    public function __construct(Event $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['isPublished'])) {
            $query->where('isPublished', $filters['isPublished']);
        }

        return $query->orderBy('from_date', 'desc')->get();
    }

    public function find(int $id): ?Event
    {
        return $this->model->find($id);
    }

    public function create(array $data): Event
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $event = $this->find($id);
        
        if (!$event) {
            return false;
        }

        return $event->update($data);
    }

    public function delete(int $id): bool
    {
        $event = $this->find($id);
        
        if (!$event) {
            return false;
        }

        return $event->delete();
    }

    public function getPublished(array $filters = []): Collection
    {
        return $this->all(array_merge($filters, ['isPublished' => 1]));
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->where(function($query) use ($startDate, $endDate) {
            $query->whereBetween('from_date', [$startDate, $endDate])
                  ->orWhereBetween('to_date', [$startDate, $endDate])
                  ->orWhere(function($q) use ($startDate, $endDate) {
                      $q->where('from_date', '<=', $startDate)
                        ->where('to_date', '>=', $endDate);
                  });
        })->orderBy('from_date', 'desc')->get();
    }
}


