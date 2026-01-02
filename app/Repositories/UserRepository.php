<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        // Exclude soft-deleted users (role = 0) by default
        if (!isset($filters['include_deleted']) || !$filters['include_deleted']) {
            $query->where('role', '!=', 0);
        }

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        return $query->orderBy('user_id', 'desc')->get();
    }

    public function find(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        // Hash password if provided and not already hashed
        if (isset($data['password'])) {
            // Check if password is already hashed (starts with $2y$ or $2a$ or $2b$)
            if (!preg_match('/^\$2[ayb]\$/', $data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
        }

        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $user = $this->find($id);
        
        if (!$user) {
            return false;
        }

        // Hash password if provided and not already hashed
        if (isset($data['password']) && !empty($data['password'])) {
            // Check if password is already hashed (starts with $2y$ or $2a$ or $2b$)
            if (!preg_match('/^\$2[ayb]\$/', $data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
        } else {
            // Don't update password if empty
            unset($data['password']);
        }

        return $user->update($data);
    }

    public function delete(int $id): bool
    {
        $user = $this->find($id);
        
        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    public function checkEmailExists(string $email, ?int $excludeId = null): bool
    {
        $query = $this->model->where('email', $email);

        if ($excludeId) {
            $query->where('user_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Soft delete user by setting role to 0
     */
    public function softDelete(int $id): bool
    {
        $user = $this->find($id);
        
        if (!$user) {
            return false;
        }

        return $user->update(['role' => 0]);
    }
}

