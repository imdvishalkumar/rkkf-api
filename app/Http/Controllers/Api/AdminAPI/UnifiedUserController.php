<?php

namespace App\Http\Controllers\Api\AdminAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\StudentService;
use App\Helpers\ApiResponseHelper;
use App\Http\Requests\AdminAPI\CreateUnifiedUserRequest;
use App\Enums\UserRole;
use Illuminate\Support\Facades\DB;
use Exception;

class UnifiedUserController extends Controller
{
    protected $userService;
    protected $studentService;
    protected $userRepository;
    protected $studentRepository;

    public function __construct(
        UserService $userService,
        StudentService $studentService,
        \App\Repositories\Contracts\UserRepositoryInterface $userRepository,
        \App\Repositories\Contracts\StudentRepositoryInterface $studentRepository
    ) {
        $this->userService = $userService;
        $this->studentService = $studentService;
        $this->userRepository = $userRepository;
        $this->studentRepository = $studentRepository;
    }

    /**
     * Create Unified User (User/Instructor field + Student record)
     * POST /api/admin/unified-users
     */
    public function store(CreateUnifiedUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $authenticatedUser = $request->user();

            if (!$authenticatedUser->isAdmin()) {
                return ApiResponseHelper::forbidden('Only super admins can create users');
            }

            $data = $request->validated();

            // Handle role mapping
            $roleInput = $data['role'];
            if ($roleInput instanceof UserRole) {
                $role = $roleInput;
            } else {
                $role = UserRole::tryFrom($roleInput) ?? UserRole::USER;
            }

            // 1. Create User via Repository (to bypass service-level checks already handled by Request)
            $userData = [
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'password' => $data['password'],
                'role' => $role->value,
            ];

            $user = $this->userRepository->create($userData);
            $user->assignRole($role->value);

            // 2. Create Student via Repository
            // Data mapping for legacy NOT NULL fields without defaults
            $studentData = [
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'password' => $data['password'],
                'selfno' => $data['mobile'],
                'active' => 1,
                'doj' => now()->format('Y-m-d'),
                'dob' => $data['dob'] ?? '2000-01-01',
                'gender' => ($data['gender'] ?? 'Male') === 'Female' ? 1 : 0,
                'branch_id' => $data['branch_id'] ?? 0,
                'belt_id' => $data['belt_id'] ?? 1,
                'address' => $data['address'] ?? 'N/A',
                'pincode' => $data['pincode'] ?? '000000',
                'profile_img' => 'default.png',
            ];

            $student = $this->studentRepository->create($studentData);

            // Assign student role if applicable
            if ($role === UserRole::USER) {
                $user->assignRole('student');
            }

            DB::commit();

            return ApiResponseHelper::success([
                'user' => [
                    'user_id' => $user->user_id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                ],
                'student' => [
                    'student_id' => $student->student_id,
                    'email' => $student->email,
                ],
                'message' => 'User and Student profile created successfully',
            ], 'User and Student created successfully');

        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponseHelper::error(
                'Failed to create unified user: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update Student Profile
     * PUT /api/admin/unified-users/student/{id}
     */
    public function updateStudentProfile(Request $request, $id)
    {
        try {
            $authenticatedUser = $request->user();

            if (!$authenticatedUser->isAdmin()) {
                return ApiResponseHelper::forbidden('Only super admins can update student profiles');
            }

            $validated = $request->validate([
                'firstname' => 'sometimes|string|max:255',
                'lastname' => 'sometimes|string|max:255',
                'dob' => 'nullable|date',
                'gender' => 'nullable|string',
                'mobile' => 'sometimes|string|max:20',
                'address' => 'nullable|string',
                'pincode' => 'nullable|string',
                'branch_id' => 'nullable|integer|exists:branch,branch_id',
                'belt_id' => 'nullable|integer|exists:belt,belt_id',
                'active' => 'sometimes|boolean',
            ]);

            // Map mobile to selfno
            if (isset($validated['mobile'])) {
                $validated['selfno'] = $validated['mobile'];
                unset($validated['mobile']);
            }

            $result = $this->studentService->updateStudent($id, $validated);

            return ApiResponseHelper::success([
                'student' => $result['student']
            ], 'Student profile updated successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error(
                'Failed to update student profile',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }
}
