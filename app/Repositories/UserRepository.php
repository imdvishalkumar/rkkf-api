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

        // Filter by role if specified
        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // Note: role = 0 is for regular users, not soft-deleted users
        // If you need soft deletes, use Laravel's built-in soft delete feature

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

        // Ensure role is an integer and valid enum value
        if (isset($data['role'])) {
            $data['role'] = (int)$data['role'];
            // Validate role value exists in enum
            if (!in_array($data['role'], [0, 1, 2], true)) {
                throw new \InvalidArgumentException("Invalid role value: {$data['role']}. Must be 0, 1, or 2.");
            }
        }

        // Use DB facade to insert directly, bypassing enum casting during insert
        // Then retrieve the model to get enum-cast attributes
        $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId($data);
        
        return $this->find($userId);
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
     * Soft delete user (hard delete for now since we don't have soft deletes enabled)
     * Note: role = 0 is for regular users, not deleted users
     */
    public function softDelete(int $id): bool
    {
        // For now, just do a hard delete
        // If you need soft deletes, enable them in the User model using SoftDeletes trait
        return $this->delete($id);
    }
}

