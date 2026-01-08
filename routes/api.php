<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\FeeApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\ExamApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\AdminAPI\SuperAdminController;
use App\Http\Controllers\Api\AdminAPI\UserManagementController;
use App\Http\Controllers\Api\AdminAPI\InstructorManagementController;
use App\Http\Controllers\Api\AdminAPI\UnifiedUserController;
use App\Http\Controllers\Api\FrontendAPI\UserController as FrontendUserController;
use App\Http\Controllers\Api\FrontendAPI\InstructorController as FrontendInstructorController;

/*
|--------------------------------------------------------------------------
| Public APIs
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/admin/login', [SuperAdminController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected APIs
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthApiController::class, 'logout']);

    Route::get('/me', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });

    // Admin Specific APIs
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Unified User Management (Creates User + Student Profile)
        Route::post('unified-users', [UnifiedUserController::class, 'store']);
        Route::put('unified-users/student/{id}', [UnifiedUserController::class, 'updateStudentProfile']);

        // Standard User & Instructor Management
        Route::apiResource('users', UserManagementController::class)->parameters(['users' => 'id']);
        Route::apiResource('instructors', InstructorManagementController::class)->parameters(['instructors' => 'id']);
    });

    // Instructor Specific APIs
    Route::middleware('role:instructor,admin')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */
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
    });

    // General/Student/Frontend APIs
    Route::middleware('role:student,instructor,admin')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        */
        Route::prefix('students')->group(function () {
            Route::get('get-by-branch', [StudentApiController::class, 'getStudentsByBranch']);
            Route::get('search', [StudentApiController::class, 'searchStudents']);
            Route::get('deactive-report', [StudentApiController::class, 'getDeactiveReport']);
            Route::post('set-status', [StudentApiController::class, 'setStatus']);
        });

        /*
        |--------------------------------------------------------------------------
        | Events
        |--------------------------------------------------------------------------
        */
        Route::prefix('events')->group(function () {
            Route::get('/', [EventApiController::class, 'index']);
            Route::get('upcoming', [EventApiController::class, 'upcoming']);
            Route::post('/', [EventApiController::class, 'store']);
            Route::get('{id}', [EventApiController::class, 'show']);
            Route::put('{id}', [EventApiController::class, 'update']);
            Route::delete('{id}', [EventApiController::class, 'destroy']);
        });

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */
        Route::prefix('products')->group(function () {
            Route::get('list', [ProductApiController::class, 'getProductList']);
            Route::post('store', [ProductApiController::class, 'store']);
            Route::get('{product_id}', [ProductApiController::class, 'show']);
            Route::get('details/{product_id}', [ProductApiController::class, 'productDetails']);
            Route::put('{product_id}', [ProductApiController::class, 'update']);
            Route::delete('{product_id}', [ProductApiController::class, 'destroy']);
            Route::put('{product_id}/variations/{variation_id}', [ProductApiController::class, 'updateVariationQty']);
        });

        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        */
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartApiController::class, 'index']);
            Route::post('/', [CartApiController::class, 'store']);
            Route::delete('{id}', [CartApiController::class, 'destroy']);
        });

        /*
        |--------------------------------------------------------------------------
        | Frontend Profile
        |--------------------------------------------------------------------------
        */
        Route::prefix('frontend')->group(function () {
            Route::put('user/{id}', [FrontendUserController::class, 'update']);
            Route::delete('user/{id}', [FrontendUserController::class, 'delete']);
            Route::put('instructor/{id}', [FrontendInstructorController::class, 'update']);
            Route::delete('instructor/{id}', [FrontendInstructorController::class, 'delete']);
        });
    });

});
