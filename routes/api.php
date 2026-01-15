<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\FeeApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\EventLikeController;
use App\Http\Controllers\Api\ExamApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\EventCommentController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\LeaveApiController;
use App\Http\Controllers\Api\AdminAPI\SuperAdminController;
use App\Http\Controllers\Api\AdminAPI\UserManagementController;
use App\Http\Controllers\Api\AdminAPI\InstructorManagementController;
use App\Http\Controllers\Api\BranchApiController;
use App\Http\Controllers\Api\CouponApiController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\InstructorApiController;

use App\Http\Controllers\Api\AdminAPI\UnifiedUserController;
use App\Http\Controllers\Api\FrontendAPI\UserController as FrontendUserController;
use App\Http\Controllers\Api\FrontendAPI\InstructorController as FrontendInstructorController;

/*
|--------------------------------------------------------------------------
| Public APIs
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/admin/login', [SuperAdminController::class, 'login']);

// Password Reset Routes (Public - No Auth Required)
Route::post('/forgot-password', [AuthApiController::class, 'forgotPassword']);
Route::post('/verify-reset-token', [AuthApiController::class, 'verifyResetToken']);
Route::post('/update-password', [AuthApiController::class, 'updatePassword']);

/*
|--------------------------------------------------------------------------
| Protected APIs
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthApiController::class, 'logout']);

    Route::get('/me', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        return response()->json([
            'user' => $user,
            'user_id' => $user?->user_id,
            'role' => $user?->role?->value,
            'token_id' => $token?->id,
            'token_abilities' => $token?->abilities ?? [],
        ]);
    });

    // Admin Specific APIs (role check done in controller)
    Route::prefix('admin')->group(function () {
        // Test route to verify authentication
        Route::get('test-auth', function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::info('Admin test-auth route hit', [
                'user' => $request->user()?->user_id,
                'token' => $request->bearerToken() ? 'present' : 'missing',
                'authenticated' => $request->user() !== null,
            ]);
            return response()->json([
                'authenticated' => $request->user() !== null,
                'user_id' => $request->user()?->user_id,
                'role' => $request->user()?->role?->value,
                'token_present' => $request->bearerToken() !== null,
            ]);
        });

        // Unified User Management (Creates User + Student Profile)
        Route::post('unified-users', [UnifiedUserController::class, 'store']);
        Route::put('unified-users/student/{id}', [UnifiedUserController::class, 'updateStudentProfile']);

        // Standard User & Instructor Management
        Route::apiResource('users', UserManagementController::class)->parameters(['users' => 'id']);
        Route::apiResource('instructors', InstructorManagementController::class)->parameters(['instructors' => 'id']);
        Route::apiResource('branches', BranchApiController::class)->parameters(['branches' => 'id']); // Added
    });

    // fetch branch
    Route::prefix('branches')->group(function () {
        Route::get('/', [BranchApiController::class, 'index']);
    });

    // Instructor Specific APIs (Only accessible by Instructor or Admin roles)
    Route::prefix('instructor')->middleware('role:instructor,admin')->group(function () {
        Route::get('branches/{id}/days', [InstructorApiController::class, 'getBranchDays']);
        Route::get('students/search', [InstructorApiController::class, 'searchStudents']);
        Route::post('attendance/count', [InstructorApiController::class, 'getAttendanceCount']);
        Route::post('fastrack/attendance', [InstructorApiController::class, 'insertFastrackAttendance']);

        // Event Attendance
        Route::get('events/for-attendance', [InstructorApiController::class, 'getEventsForAttendance']);
        Route::get('events/{id}/students', [InstructorApiController::class, 'getStudentsForEvent']);
        Route::post('events/attendance', [InstructorApiController::class, 'insertEventAttendance']);

        // Exam Attendance
        Route::get('exams/for-attendance', [InstructorApiController::class, 'getExamsForAttendance']);
        Route::get('exams/{id}/students', [InstructorApiController::class, 'getStudentsForExam']);
        Route::post('exams/attendance', [InstructorApiController::class, 'insertExamAttendance']);
        Route::get('exams/{id}', [InstructorApiController::class, 'getExamDetails']);
    });

    // Attendance APIs (role check done in controller)
    Route::prefix('attendance')->group(function () {
        Route::get('get-students', [AttendanceApiController::class, 'getStudents']);
        Route::get('get-attendance', [AttendanceApiController::class, 'getAttendance']);
        Route::post('insert', [AttendanceApiController::class, 'insertAttendance']);

        Route::prefix('additional')->group(function () {
            Route::get('get-students', [AttendanceApiController::class, 'getAdditionalStudents']);
            Route::get('get-attendance', [AttendanceApiController::class, 'getAdditionalAttendance']);
            Route::post('insert', [AttendanceApiController::class, 'insertAdditionalAttendance']);
        });

        Route::get('log', [AttendanceApiController::class, 'getAttendanceLog']);
        Route::get('view', [AttendanceApiController::class, 'viewAttendance']);
    });

    // General/Student/Frontend APIs
    Route::prefix('students')->group(function () {
        Route::get('get-by-branch', [StudentApiController::class, 'getStudentsByBranch']);
        Route::get('search', [StudentApiController::class, 'searchStudents']);
        Route::get('deactive-report', [StudentApiController::class, 'getDeactiveReport']);
        Route::post('set-status', [StudentApiController::class, 'setStatus']);
        Route::put('profile', [StudentApiController::class, 'updateProfile']);
        Route::get('profile', [StudentApiController::class, 'getProfile']);
        Route::get('attendance', [StudentApiController::class, 'getAttendanceOverview']);
        Route::get('status', [StudentApiController::class, 'getStatus']);
        Route::get('exam-results', [StudentApiController::class, 'getExamResults']);
    });

    // Coupon Validation
    Route::get('coupons/validate', [CouponApiController::class, 'validate']);

    // File Upload
    Route::post('upload', [FileUploadController::class, 'upload']);

    Route::prefix('events')->group(function () {
        Route::get('/', [EventApiController::class, 'index']);
        Route::get('upcoming', [EventApiController::class, 'upcoming']);
        Route::post('/', [EventApiController::class, 'store']);
        Route::get('/details/{id}', [EventApiController::class, 'show']);
        Route::put('{id}', [EventApiController::class, 'update']);
        Route::delete('{id}', [EventApiController::class, 'destroy']);

        // Event like endpoints (require authentication)
        Route::get('{event_id}/like', [EventLikeController::class, 'getLikeStatus']);
        Route::post('{event_id}/like', [EventLikeController::class, 'toggleLike']);

        // Event comment endpoints (require authentication)
        Route::post('/{event_id}/comments', [EventCommentController::class, 'store']);
        Route::get('/{event_id}/comments', [EventCommentController::class, 'index']);
    });

    // Comment Likes (moved outside events to avoid conflict with event likes)
    Route::post('comments/{comment_id}/like', [EventCommentController::class, 'toggleLike']);

    Route::prefix('products')->group(function () {
        Route::get('list', [ProductApiController::class, 'getProductList']);
        Route::post('store', [ProductApiController::class, 'store']);
        Route::get('{product_id}', [ProductApiController::class, 'show']);
        Route::get('show/{product_id}', [ProductApiController::class, 'productDetails']);
        Route::put('{product_id}', [ProductApiController::class, 'update']);
        Route::delete('{product_id}', [ProductApiController::class, 'destroy']);
        Route::put('{product_id}/variations/{variation_id}', [ProductApiController::class, 'updateVariationQty']);
    });

    Route::apiResource('product-categories', \App\Http\Controllers\Api\ProductCategoryController::class);

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartApiController::class, 'index']);
        Route::post('/', [CartApiController::class, 'store']);
        Route::post('quantity', [CartApiController::class, 'updateQuantity']);
        Route::delete('{id}', [CartApiController::class, 'destroy']);
    });

    Route::prefix('frontend')->group(function () {
        Route::put('user/{id}', [FrontendUserController::class, 'update']);
        Route::delete('user/{id}', [FrontendUserController::class, 'delete']);
        Route::put('instructor/{id}', [FrontendInstructorController::class, 'update']);
        Route::delete('instructor/{id}', [FrontendInstructorController::class, 'delete']);
    });

    // Fee Payment APIs
    Route::prefix('fees')->group(function () {
        // Get due fees for authenticated student
        Route::get('due', [FeeApiController::class, 'getDueFees']);
        // Fees summary (for Fees Summary screen)
        Route::get('summary', [FeeApiController::class, 'getSummary']);
        // Payment history (for Payment History screen)
        Route::get('history', [FeeApiController::class, 'getHistory']);
        // Payment flow
        Route::post('payment/initiate', [FeeApiController::class, 'initiatePayment']);
        Route::post('payment/verify', [FeeApiController::class, 'verifyPayment']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('get-orders', [OrderApiController::class, 'getOrders']); // Admin/General list
        Route::get('my-orders', [OrderApiController::class, 'myOrders']); // Student specific
        Route::post('viewed', [OrderApiController::class, 'markViewed']);
        Route::post('delivered', [OrderApiController::class, 'markDelivered']);
        // Delete product route was inside OrderController? Keeping it if legacy requires.
        Route::post('delete-product', [OrderApiController::class, 'deleteProduct']);
        Route::post('review', [OrderApiController::class, 'submitReview']);
    });

    // Leave Management APIs
    Route::prefix('leaves')->group(function () {
        Route::post('apply', [LeaveApiController::class, 'apply']);
        Route::get('history', [LeaveApiController::class, 'history']);
    });

    // Notifications API
    Route::get('notifications', [NotificationApiController::class, 'index']);

});

/*
|--------------------------------------------------------------------------
| Public Webhook Routes (No Auth Required)
|--------------------------------------------------------------------------
*/

// Razorpay webhook for payment notifications
Route::post('/razorpay/webhook', [FeeApiController::class, 'handleWebhook']);
