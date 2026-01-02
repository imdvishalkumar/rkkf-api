<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Validation\ValidationException;

class UserApiController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Create a new user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role' => 'required|integer|in:1,2',
                'mobile' => 'nullable|string|max:20',
            ]);

            $result = $this->userService->createUser($validated);

            return ApiResponseHelper::success([
                'user' => [
                    'user_id' => $result['user']->user_id,
                    'firstname' => $result['user']->firstname,
                    'lastname' => $result['user']->lastname,
                    'email' => $result['user']->email,
                    'mobile' => $result['user']->mobile,
                    'role' => $result['user']->role,
                ],
            ], $result['message']);

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to create user',
                $e->getCode() ?: 500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * List users (requires auth)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->all();

            $users = $this->userService->getAllUsers($filters);

            return ApiResponseHelper::success([
                'users' => $users,
            ]);

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to fetch users',
                $e->getCode() ?: 500,
                ['error' => $e->getMessage()]
            );
        }
    }
}

