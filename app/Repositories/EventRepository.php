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
        return $this->model->find($id);
    }

    public function getAll(int $perPage = 15, ?int $categoryId = null)
    {
        $query = $this->model->with('category')->orderBy('from_date', 'desc');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->paginate($perPage);
    }

    public function getUpcoming()
    {
        return $this->model->upcoming()->get();
    }
}
