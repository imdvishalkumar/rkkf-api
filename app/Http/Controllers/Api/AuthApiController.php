<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Student;
use App\Helpers\ApiResponseHelper;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Enums\UserRole;
use App\Http\Requests\RegisterRequest;

class AuthApiController extends Controller
{
    /**
     * Login user and generate API token
     * 
     * Role-Endpoint Binding:
     * - user: Allowed
     * - instructor: Allowed
     * - admin: NOT ALLOWED (must use /admin/login)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return ApiResponseHelper::error(
                    'Invalid credentials',
                    401,
                    ['email' => ['The provided credentials are incorrect.']]
                );
            }

            // Check password - support both plain text (legacy) and hashed passwords
            $passwordValid = false;

            // Check plain text password (for existing system compatibility)
            if ($user->password === $request->password) {
                $passwordValid = true;
                // Optionally hash the password for future use
                // $user->password = Hash::make($request->password);
                // $user->save();
            }
            // Check hashed password (for new Laravel system)
            elseif (Hash::check($request->password, $user->password)) {
                $passwordValid = true;
            }

            if (!$passwordValid) {
                return ApiResponseHelper::error(
                    'Invalid credentials',
                    401,
                    ['password' => ['The provided credentials are incorrect.']]
                );
            }

            // Validate Role - Enforce Role-Endpoint Binding
            $role = $user->role; // Already cast to UserRole enum

            if (!in_array($role, [UserRole::USER, UserRole::INSTRUCTOR])) {
                Log::warning('RBAC violation', [
                    'email' => $request->email,
                    'endpoint' => $request->path(),
                    'attempted_role' => $role->value,
                ]);
                return ApiResponseHelper::forbidden('Admin accounts must login via /admin/login');
            }

            // Spatie Role Safety Validation - Check role exists before assignment
            $spatieRoleName = $role->spatieRole();

            if (!User::validateSpatieRoleExists($spatieRoleName)) {
                Log::critical('Role misconfiguration detected', [
                    'email' => $request->email,
                    'endpoint' => $request->path(),
                    'role' => $role->value,
                    'spatie_role_name' => $spatieRoleName,
                ]);
                abort(500, 'System role misconfigured');
            }

            // Ensure user has Spatie role assigned (validates existence first)
            $user->ensureSpatieRole();

            // Revoke all existing tokens (optional - for single device login)
            // $user->tokens()->delete();

            // Create token with role-specific scope
            $tokenScope = $role->value; // 'user' or 'instructor'
            $token = $user->createToken('auth-token', [$tokenScope])->plainTextToken;

            // Get role string value (user->role is now a UserRole enum, ->value gives the string)
            $roleString = $user->role instanceof \App\Enums\UserRole ? $user->role->value : (string) $user->role;
            $userKey = $roleString; // Use role string as response key

            // Get profile image from students table if email matches
            $profileImgUrl = null;
            $student = Student::where('email', $user->email)->first();

            if ($student && !empty($student->profile_img) && $student->profile_img !== 'default.png') {
                // Student has profile image - use the same path format as unified-users API
                // File is stored in storage/app/public/profile_images/ and accessible via /storage/profile_images/
                $profileImgPath = 'profile_images/' . $student->profile_img;
                if (Storage::disk('public')->exists($profileImgPath)) {
                    // Use Storage URL which returns: /storage/profile_images/filename.jpg
                    $profileImgUrl = Storage::disk('public')->url($profileImgPath);
                } else {
                    // Image file doesn't exist in storage, use default
                    $defaultImagePath = 'profile_images/default.png';
                    if (Storage::disk('public')->exists($defaultImagePath)) {
                        $profileImgUrl = Storage::disk('public')->url($defaultImagePath);
                    } else {
                        $profileImgUrl = asset('images/default-avatar.png');
                    }
                }
            } else {
                // No student record, no profile_img, or default.png - use default image
                $defaultImagePath = 'profile_images/default.png';
                if (Storage::disk('public')->exists($defaultImagePath)) {
                    $profileImgUrl = Storage::disk('public')->url($defaultImagePath);
                } else {
                    // Use a generic placeholder or default avatar URL
                    $profileImgUrl = asset('images/default-avatar.png');
                }
            }

            return ApiResponseHelper::success([
                $userKey => [
                    'user_id' => $user->user_id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => $roleString,
                    'gr_no' => $student ? $student->gr_no : null,
                    'profile_img' => $profileImgUrl,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Login successful');

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Login failed',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }


    /**
     * Register user and generate API token
     * 
     * Supports multipart/form-data with file upload for profile image
     * 
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $profileImgPath = null;

        try {
            $data = $request->validated();

            // Handle profile image upload
            if ($request->hasFile('profile_img')) {
                $file = $request->file('profile_img');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $profileImgPath = $file->storeAs('profile_images', $fileName, 'public');
            }

            // Handle role mapping
            $roleInput = $data['role'];
            if ($roleInput instanceof UserRole) {
                $role = $roleInput;
            } else {
                $role = UserRole::tryFrom($roleInput) ?? UserRole::USER;
            }

            // Spatie Role Safety Validation - Check role exists before assignment
            $spatieRoleName = $role->spatieRole();

            if (!User::validateSpatieRoleExists($spatieRoleName)) {
                // If file was uploaded but role validation fails, delete it
                if ($profileImgPath) {
                    Storage::disk('public')->delete($profileImgPath);
                }

                Log::critical('Role misconfiguration detected', [
                    'email' => $data['email'],
                    'endpoint' => $request->path(),
                    'role' => $role->value,
                    'spatie_role_name' => $spatieRoleName,
                ]);
                abort(500, 'System role misconfigured');
            }

            // Prepare user data
            // Note: users table doesn't have profile_img column, only students table has it
            $userData = [
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'password' => $data['password'],
                'role' => $role->value,
            ];

            // Note: profile_img is not stored in users table
            // If profile image is uploaded, it's saved to storage but not in DB
            // Profile images are only stored in students table (via unified-users endpoint)

            // Create user via repository
            $userRepository = app(\App\Repositories\Contracts\UserRepositoryInterface::class);
            $user = $userRepository->create($userData);

            // Ensure user has Spatie role assigned (validates existence first)
            $user->ensureSpatieRole();

            // Create token with role-specific scope
            $tokenScope = $role->value;
            $token = $user->createToken('auth-token', [$tokenScope])->plainTextToken;

            // Get role string value
            $roleString = $user->role instanceof \App\Enums\UserRole ? $user->role->value : (string) $user->role;
            $userKey = $roleString;

            // Get profile image from students table if email matches
            $profileImgUrl = null;
            $student = Student::where('email', $user->email)->first();

            if ($student && !empty($student->profile_img)) {
                // Student has profile image - construct full path
                $profileImgPath = 'profile_images/' . $student->profile_img;
                if (Storage::disk('public')->exists($profileImgPath)) {
                    $profileImgUrl = Storage::disk('public')->url($profileImgPath);
                } else {
                    // Image file doesn't exist, use default
                    $defaultImagePath = 'profile_images/default.png';
                    if (Storage::disk('public')->exists($defaultImagePath)) {
                        $profileImgUrl = Storage::disk('public')->url($defaultImagePath);
                    } else {
                        $profileImgUrl = asset('images/default-avatar.png');
                    }
                }
            } else {
                // No student record or no profile_img, use default
                // Note: If profile image was uploaded during registration, it's saved to storage
                // but not in users table. Profile images are only stored in students table (via unified-users endpoint)
                $defaultImagePath = 'profile_images/default.png';
                if (Storage::disk('public')->exists($defaultImagePath)) {
                    $profileImgUrl = Storage::disk('public')->url($defaultImagePath);
                } else {
                    // Use a generic placeholder or default avatar URL
                    $profileImgUrl = asset('images/default-avatar.png');
                }
            }

            return ApiResponseHelper::success([
                $userKey => [
                    'user_id' => $user->user_id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => $roleString,
                    'gr_no' => $student ? $student->gr_no : null,
                    'profile_img' => $profileImgUrl,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Registration successful');

        } catch (ValidationException $e) {
            // If file was uploaded but validation fails, delete it
            if (isset($profileImgPath) && $profileImgPath) {
                Storage::disk('public')->delete($profileImgPath);
            }

            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            // If file was uploaded but registration fails, delete it
            if (isset($profileImgPath) && $profileImgPath) {
                Storage::disk('public')->delete($profileImgPath);
            }

            return ApiResponseHelper::error(
                'Registration failed',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Unified logout endpoint for all roles
     * 
     * Supports optional parameter to logout from all devices:
     * - Default: Revokes current token only
     * - With "all_devices": true in request body, revokes all tokens
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $logoutAll = $request->input('all_devices', false);

            if ($logoutAll) {
                // Revoke all tokens for the user (logout from all devices)
                $user->tokens()->delete();
                $message = 'Logged out from all devices successfully';
            } else {
                // Revoke only the current token (logout from current device)
                $request->user()->currentAccessToken()->delete();
                $message = 'Logged out successfully';
            }

            return ApiResponseHelper::success(null, $message);
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Logout failed',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Send password reset token via email
     * POST /api/forgot-password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $email = trim($request->email);

            // Find student by email
            $student = Student::where('email', $email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Invalid Email Address!', 422);
            }

            // Generate reset token
            $token = md5($email) . rand(10, 9999);
            $expDate = now()->addDay(); // 24 hours expiry

            // Update student with reset token
            $student->reset_link_token = $token;
            $student->exp_date = $expDate;
            $student->save();

            // Send email with token
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Your password reset token is: {$token}\n\nThis token will expire in 24 hours.",
                    function ($message) use ($email) {
                        $message->to($email)
                            ->subject('RKKF - Password Reset Token');
                    }
                );

                return ApiResponseHelper::success([
                    'done' => 1,
                ], 'We have successfully sent you password reset token to your email.');

            } catch (\Exception $mailException) {
                Log::error('Failed to send password reset email', [
                    'email' => $email,
                    'error' => $mailException->getMessage()
                ]);
                return ApiResponseHelper::error('Failed to send email!', 422);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to process request',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Verify password reset token
     * POST /api/verify-reset-token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyResetToken(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
            ]);

            $email = trim($request->email);
            $token = trim($request->token);

            // Find student with matching email and token
            $student = Student::where('email', $email)
                ->where('reset_link_token', $token)
                ->first();

            if (!$student) {
                return ApiResponseHelper::success([
                    'tokenMatched' => false,
                ], 'Incorrect Token.');
            }

            // Check if token has expired
            $currentDate = now();
            if ($student->exp_date && $student->exp_date >= $currentDate) {
                return ApiResponseHelper::success([
                    'tokenMatched' => true,
                ], 'Token validated successfully.');
            } else {
                return ApiResponseHelper::success([
                    'tokenMatched' => false,
                ], 'Token Expired!');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to verify token',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update password after reset token verification
     * POST /api/update-password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            $email = trim($request->email);
            $password = $request->password;

            // Find student by email
            $student = Student::where('email', $email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Invalid Email Address!', 422);
            }

            // Update password and clear reset token
            $student->password = Hash::make($password);
            $student->reset_link_token = null;
            $student->exp_date = null;
            $student->save();

            // Also update User table if exists
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->password = Hash::make($password);
                $user->save();
            }

            return ApiResponseHelper::success([
                'updated' => true,
            ], 'Password Updated Successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Error while updating password',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
