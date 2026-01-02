<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers(array $filters = [])
    {
        return $this->userRepository->all($filters);
    }

    public function getUserById(int $id)
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            throw new Exception('User not found', 404);
        }

        return $user;
    }

    public function getUserByEmail(string $email)
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            throw new Exception('User not found', 404);
        }

        return $user;
    }

    public function createUser(array $data): array
    {
        DB::beginTransaction();
        
        try {
            if ($this->userRepository->checkEmailExists($data['email'])) {
                throw new Exception('Email already exists', 422);
            }

            $user = $this->userRepository->create($data);

            DB::commit();

            return [
                'user' => $user,
                'message' => 'User created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateUser(int $id, array $data): array
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            throw new Exception('User not found', 404);
        }

        if (isset($data['email']) && $data['email'] !== $user->email) {
            if ($this->userRepository->checkEmailExists($data['email'], $id)) {
                throw new Exception('Email already exists', 422);
            }
        }

        $updated = $this->userRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update user', 500);
        }

        return [
            'user' => $this->userRepository->find($id),
            'message' => 'User updated successfully'
        ];
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            throw new Exception('User not found', 404);
        }

        // Use soft delete (set role to 0) instead of hard delete
        return $this->userRepository->softDelete($id);
    }

    /**
     * Hard delete user (permanent deletion)
     */
    public function hardDeleteUser(int $id): bool
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            throw new Exception('User not found', 404);
        }

        return $this->userRepository->delete($id);
    }
}

