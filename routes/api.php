<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\FeeApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\ExamApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\CategoryApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\FrontendAPI\UserController as FrontendUserController;
use App\Http\Controllers\Api\FrontendAPI\InstructorController as FrontendInstructorController;
use App\Http\Controllers\Api\FrontendAPI\AuthController as FrontendAuthController;
use App\Http\Controllers\Api\AdminAPI\SuperAdminController;
use App\Http\Controllers\Api\AdminAPI\UserManagementController;
use App\Http\Controllers\Api\AdminAPI\InstructorManagementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ==================== AUTHENTICATION ROUTES (Public) ====================
Route::post('/login', [AuthApiController::class, 'login'])->name('api.login');

// ==================== USER MANAGEMENT ROUTES (Public - for registration) ====================
Route::post('/users', [UserApiController::class, 'store'])->name('api.users.store');

// ==================== FRONTEND API ROUTES (Public - Registration) ====================
// Note: User and Instructor registration is handled by Admin. 
// Login is unified at /api/login for all roles (User, Instructor, Admin)
// Route::post('/frontend/user/register', [FrontendUserController::class, 'register'])->name('api.frontend.user.register');
// Route::post('/frontend/instructor/register', [FrontendInstructorController::class, 'register'])->name('api.frontend.instructor.register');

// ==================== ADMIN API ROUTES (Public - Super Admin Login) ====================
// Note: Admin login is also handled by unified /api/login endpoint
// Route::post('/admin/super-admin/login', [SuperAdminController::class, 'login'])->name('api.admin.super-admin.login');

// ==================== PROTECTED API ROUTES (Require Token) ====================
Route::middleware(['auth:sanctum'])->group(function () {

    // List users (requires authentication)
    Route::get('/users', [UserApiController::class, 'index'])->name('api.users.index');
    // Unified logout endpoint (works for all roles)
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('api.logout');

    // ==================== ATTENDANCE API ROUTES ====================

    // Get students for attendance by branch
    Route::get('/attendance/get-students', [AttendanceApiController::class, 'getStudents'])->name('api.attendance.get-students');

    // Get attendance by branch and date
    Route::get('/attendance/get-attendance', [AttendanceApiController::class, 'getAttendance'])->name('api.attendance.get-attendance');

    // Insert/Update attendance
    Route::post('/attendance/insert', [AttendanceApiController::class, 'insertAttendance'])->name('api.attendance.insert');

    // Additional attendance - get students
    Route::get('/attendance/additional/get-students', [AttendanceApiController::class, 'getAdditionalStudents'])->name('api.attendance.additional.get-students');

    // Additional attendance - get attendance
    Route::get('/attendance/additional/get-attendance', [AttendanceApiController::class, 'getAdditionalAttendance'])->name('api.attendance.additional.get-attendance');

    // Additional attendance - insert/update
    Route::post('/attendance/additional/insert', [AttendanceApiController::class, 'insertAdditionalAttendance'])->name('api.attendance.additional.insert');

    // Attendance log
    Route::get('/attendance/log', [AttendanceApiController::class, 'getAttendanceLog'])->name('api.attendance.log');

    // View attendance
    Route::get('/attendance/view', [AttendanceApiController::class, 'viewAttendance'])->name('api.attendance.view');

    // Exam attendance - get students
    Route::get('/exam-attendance/get-students', [AttendanceApiController::class, 'getExamAttendanceStudents'])->name('api.exam-attendance.get-students');

    // Exam attendance - insert/update
    Route::post('/exam-attendance/insert', [AttendanceApiController::class, 'insertExamAttendance'])->name('api.exam-attendance.insert');

    // Event attendance - get students
    Route::get('/event-attendance/get-students', [AttendanceApiController::class, 'getEventAttendanceStudents'])->name('api.event-attendance.get-students');

    // Event attendance - insert/update
    Route::post('/event-attendance/insert', [AttendanceApiController::class, 'insertEventAttendance'])->name('api.event-attendance.insert');

    // ==================== FEES API ROUTES ====================

    // Get student fees info by GR number
    Route::get('/fees/get-student-info', [FeeApiController::class, 'getStudentInfo'])->name('api.fees.get-student-info');

    // Get fees list with filters
    Route::get('/fees/get-fees', [FeeApiController::class, 'getFees'])->name('api.fees.get-fees');

    // Delete fee
    Route::post('/fees/delete', [FeeApiController::class, 'deleteFee'])->name('api.fees.delete');

    // Enter fees - get student by GR number
    Route::get('/fees/enter/get-student', [FeeApiController::class, 'getStudentForEnterFees'])->name('api.fees.enter.get-student');

    // Enter fees - old fees
    Route::get('/fees/enter-old/get-student', [FeeApiController::class, 'getStudentForOldFees'])->name('api.fees.enter-old.get-student');

    // Enter exam fees
    Route::post('/fees/enter-exam', [FeeApiController::class, 'enterExamFees'])->name('api.fees.enter-exam');

    // View combined fees
    Route::get('/fees/combined', [FeeApiController::class, 'getCombinedFees'])->name('api.fees.combined');

    // View fees without admission fees
    Route::get('/fees/view-without-admission', [FeeApiController::class, 'getFeesWithoutAdmission'])->name('api.fees.view-without-admission');

    // Disable fees - get student info
    Route::get('/fees/disable/get-student', [FeeApiController::class, 'getStudentForDisable'])->name('api.fees.disable.get-student');

    // Fix payment entry
    Route::post('/fees/fix-payment', [FeeApiController::class, 'fixPaymentEntry'])->name('api.fees.fix-payment');

    // Payment report
    Route::get('/fees/payment-report', [FeeApiController::class, 'getPaymentReport'])->name('api.fees.payment-report');

    // Full report
    Route::get('/fees/full-report', [FeeApiController::class, 'getFullReport'])->name('api.fees.full-report');

    // ==================== STUDENT API ROUTES ====================

    // Get students by branch
    Route::get('/students/get-by-branch', [StudentApiController::class, 'getStudentsByBranch'])->name('api.students.get-by-branch');

    // Get students by name or GR number
    Route::get('/students/search', [StudentApiController::class, 'searchStudents'])->name('api.students.search');

    // Get students for additional attendance
    Route::get('/students/get-for-additional', [StudentApiController::class, 'getStudentsForAdditional'])->name('api.students.get-for-additional');

    // Get students for fastrack
    Route::get('/students/get-for-fastrack', [StudentApiController::class, 'getStudentsForFastrack'])->name('api.students.get-for-fastrack');

    // Deactive report
    Route::get('/students/deactive-report', [StudentApiController::class, 'getDeactiveReport'])->name('api.students.deactive-report');

    // View foundation
    Route::get('/students/view-foundation', [StudentApiController::class, 'getFoundationStudents'])->name('api.students.view-foundation');

    // Set status (activate/deactivate)
    Route::post('/students/set-status', [StudentApiController::class, 'setStatus'])->name('api.students.set-status');

    // ==================== ORDER API ROUTES ====================

    // Get orders list
    Route::get('/orders/get-orders', [OrderApiController::class, 'getOrders'])->name('api.orders.get-orders');

    // Mark order as viewed
    Route::post('/orders/mark-viewed', [OrderApiController::class, 'markViewed'])->name('api.orders.mark-viewed');

    // Mark order as delivered
    Route::post('/orders/mark-delivered', [OrderApiController::class, 'markDelivered'])->name('api.orders.mark-delivered');

    // ==================== EXAM API ROUTES ====================

    // Get eligible students for exam
    Route::get('/exam/get-eligible-students', [ExamApiController::class, 'getEligibleStudents'])->name('api.exam.get-eligible-students');

    // Set exam eligibility
    Route::post('/exam/set-eligibility', [ExamApiController::class, 'setEligibility'])->name('api.exam.set-eligibility');

    // Get exam applied students
    Route::get('/exam/get-applied', [ExamApiController::class, 'getExamApplied'])->name('api.exam.get-applied');

    // Exam result report
    Route::get('/exam/result-report', [ExamApiController::class, 'getResultReport'])->name('api.exam.result-report');

    // Special exam - set eligibility
    Route::post('/exam/special/set-eligibility', [ExamApiController::class, 'setSpecialEligibility'])->name('api.exam.special.set-eligibility');

    // ==================== EVENT API ROUTES ====================

    Route::prefix('events')->group(function () {
        // Custom routes must be defined before apiResource to avoid conflict with {event} parameter
        Route::get('upcoming', [EventApiController::class, 'upcoming'])->name('api.events.upcoming');
    });

    // CRUD for Events (automatically prefixes with 'events')
    Route::apiResource('events', EventApiController::class);

    // Legacy/Specific routes (keeping singular 'event' prefix as per old code reference or user context? 
    // User asked for "Events Prefix" likely for the new stuff. 
    // The legacy routes below use 'event' (singular). leaving them as is or grouping them if desired, 
    // but sticking to requested scope).
    Route::get('/event/get-eligible-students', [EventApiController::class, 'getEligibleStudents'])->name('api.event.get-eligible-students');
    Route::get('/event/get-applied', [EventApiController::class, 'getEventApplied'])->name('api.event.get-applied');

    // ==================== PRODUCT API ROUTES ====================

    // Get product list with filters (belt_id)
    Route::get('/products/list', [ProductApiController::class, 'getProductList'])->name('api.products.list');

    // Delete product
    Route::post('/products/delete', [OrderApiController::class, 'deleteProduct'])->name('api.products.delete');

    // ==================== ATTENDANCE LOG API ROUTES ====================

    // Get attendance log
    Route::get('/attendance-log', [AttendanceApiController::class, 'getAttendanceLog'])->name('api.attendance-log');

    // ==================== VIEW ATTENDANCE API ROUTES ====================

    // View attendance with filters
    Route::get('/view-attendance', [AttendanceApiController::class, 'viewAttendance'])->name('api.view-attendance');

    // ==================== EXAM RESULT REPORT API ROUTES ====================

    // Get exam result report (duplicate - using exam/result-report instead)
    // Route::get('/exam-result-report', [ExamApiController::class, 'getResultReport'])->name('api.exam-result-report');

    // ==================== DEACTIVE REPORT API ROUTES ====================

    // Get deactive students report (duplicate - using students/deactive-report instead)
    // Route::get('/deactive-report', [StudentApiController::class, 'getDeactiveReport'])->name('api.deactive-report');

    // ==================== SET STATUS API ROUTES ====================

    // Set student/fee status (call flag) (duplicate - using students/set-status instead)
    // Route::post('/set-status', [StudentApiController::class, 'setStatus'])->name('api.set-status');

    // ==================== FRONTEND API ROUTES (Protected) ====================

    // User Management (Users can manage their own profile)
    Route::put('/frontend/user/{id}', [FrontendUserController::class, 'update'])->name('api.frontend.user.update');
    Route::delete('/frontend/user/{id}', [FrontendUserController::class, 'delete'])->name('api.frontend.user.delete');

    // Instructor Management (Instructors can manage their own profile)
    Route::put('/frontend/instructor/{id}', [FrontendInstructorController::class, 'update'])->name('api.frontend.instructor.update');
    Route::delete('/frontend/instructor/{id}', [FrontendInstructorController::class, 'delete'])->name('api.frontend.instructor.delete');

    // ==================== ADMIN API ROUTES (Protected - Super Admin Only) ====================

    // Super Admin Management
    Route::post('/admin/super-admin/register', [SuperAdminController::class, 'register'])->name('api.admin.super-admin.register');
    Route::put('/admin/super-admin/{id}', [SuperAdminController::class, 'update'])->name('api.admin.super-admin.update');
    Route::delete('/admin/super-admin/{id}', [SuperAdminController::class, 'delete'])->name('api.admin.super-admin.delete');

    // User Management (Admin can manage all users)
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('api.admin.users.index');
    Route::get('/admin/users/{id}', [UserManagementController::class, 'show'])->name('api.admin.users.show');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('api.admin.users.store');
    Route::put('/admin/users/{id}', [UserManagementController::class, 'update'])->name('api.admin.users.update');
    Route::delete('/admin/users/{id}', [UserManagementController::class, 'destroy'])->name('api.admin.users.destroy');

    // Instructor Management (Admin can manage all instructors)
    Route::get('/admin/instructors', [InstructorManagementController::class, 'index'])->name('api.admin.instructors.index');
    Route::get('/admin/instructors/{id}', [InstructorManagementController::class, 'show'])->name('api.admin.instructors.show');
    Route::post('/admin/instructors', [InstructorManagementController::class, 'store'])->name('api.admin.instructors.store');
    Route::put('/admin/instructors/{id}', [InstructorManagementController::class, 'update'])->name('api.admin.instructors.update');
    Route::delete('/admin/instructors/{id}', [InstructorManagementController::class, 'destroy'])->name('api.admin.instructors.destroy');

    // Category Management (Admin can manage categories)
    Route::apiResource('categories', CategoryApiController::class);

});

// Public API routes (if any)

