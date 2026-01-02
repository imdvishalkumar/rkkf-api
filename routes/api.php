<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\FeeApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ExamApiController;
use App\Http\Controllers\Api\EventApiController;
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
// User Registration & Login
Route::post('/frontend/user/register', [FrontendUserController::class, 'register'])->name('api.frontend.user.register');
Route::post('/frontend/user/login', [FrontendAuthController::class, 'loginUser'])->name('api.frontend.user.login');

// Instructor Registration & Login
Route::post('/frontend/instructor/register', [FrontendInstructorController::class, 'register'])->name('api.frontend.instructor.register');
Route::post('/frontend/instructor/login', [FrontendAuthController::class, 'loginInstructor'])->name('api.frontend.instructor.login');

// ==================== ADMIN API ROUTES (Public - Super Admin Login) ====================
Route::post('/admin/super-admin/login', [SuperAdminController::class, 'login'])->name('api.admin.super-admin.login');

// ==================== PROTECTED API ROUTES (Require Token) ====================
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Authentication routes (require token)
    Route::get('/me', [AuthApiController::class, 'me'])->name('api.me');
    // List users (requires authentication)
    Route::get('/users', [UserApiController::class, 'index'])->name('api.users.index');
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('api.logout');
    Route::post('/logout-all', [AuthApiController::class, 'logoutAll'])->name('api.logout-all');
    
    // ==================== ATTENDANCE API ROUTES ====================
    
    // Get students for attendance by branch
    Route::post('/attendance/get-students', [AttendanceApiController::class, 'getStudents'])->name('api.attendance.get-students');
    
    // Get attendance by branch and date
    Route::post('/attendance/get-attendance', [AttendanceApiController::class, 'getAttendance'])->name('api.attendance.get-attendance');
    
    // Insert/Update attendance
    Route::post('/attendance/insert', [AttendanceApiController::class, 'insertAttendance'])->name('api.attendance.insert');
    
    // Additional attendance - get students
    Route::post('/attendance/additional/get-students', [AttendanceApiController::class, 'getAdditionalStudents'])->name('api.attendance.additional.get-students');
    
    // Additional attendance - get attendance
    Route::post('/attendance/additional/get-attendance', [AttendanceApiController::class, 'getAdditionalAttendance'])->name('api.attendance.additional.get-attendance');
    
    // Additional attendance - insert/update
    Route::post('/attendance/additional/insert', [AttendanceApiController::class, 'insertAdditionalAttendance'])->name('api.attendance.additional.insert');
    
    // Attendance log
    Route::post('/attendance/log', [AttendanceApiController::class, 'getAttendanceLog'])->name('api.attendance.log');
    
    // View attendance
    Route::post('/attendance/view', [AttendanceApiController::class, 'viewAttendance'])->name('api.attendance.view');
    
    // Exam attendance - get students
    Route::post('/exam-attendance/get-students', [AttendanceApiController::class, 'getExamAttendanceStudents'])->name('api.exam-attendance.get-students');
    
    // Exam attendance - insert/update
    Route::post('/exam-attendance/insert', [AttendanceApiController::class, 'insertExamAttendance'])->name('api.exam-attendance.insert');
    
    // Event attendance - get students
    Route::post('/event-attendance/get-students', [AttendanceApiController::class, 'getEventAttendanceStudents'])->name('api.event-attendance.get-students');
    
    // Event attendance - insert/update
    Route::post('/event-attendance/insert', [AttendanceApiController::class, 'insertEventAttendance'])->name('api.event-attendance.insert');
    
    // ==================== FEES API ROUTES ====================
    
    // Get student fees info by GR number
    Route::post('/fees/get-student-info', [FeeApiController::class, 'getStudentInfo'])->name('api.fees.get-student-info');
    
    // Get fees list with filters
    Route::post('/fees/get-fees', [FeeApiController::class, 'getFees'])->name('api.fees.get-fees');
    
    // Delete fee
    Route::post('/fees/delete', [FeeApiController::class, 'deleteFee'])->name('api.fees.delete');
    
    // Enter fees - get student by GR number
    Route::post('/fees/enter/get-student', [FeeApiController::class, 'getStudentForEnterFees'])->name('api.fees.enter.get-student');
    
    // Enter fees - old fees
    Route::post('/fees/enter-old/get-student', [FeeApiController::class, 'getStudentForOldFees'])->name('api.fees.enter-old.get-student');
    
    // Enter exam fees
    Route::post('/fees/enter-exam', [FeeApiController::class, 'enterExamFees'])->name('api.fees.enter-exam');
    
    // View combined fees
    Route::post('/fees/combined', [FeeApiController::class, 'getCombinedFees'])->name('api.fees.combined');
    
    // View fees without admission fees
    Route::post('/fees/view-without-admission', [FeeApiController::class, 'getFeesWithoutAdmission'])->name('api.fees.view-without-admission');
    
    // Disable fees - get student info
    Route::post('/fees/disable/get-student', [FeeApiController::class, 'getStudentForDisable'])->name('api.fees.disable.get-student');
    
    // Fix payment entry
    Route::post('/fees/fix-payment', [FeeApiController::class, 'fixPaymentEntry'])->name('api.fees.fix-payment');
    
    // Payment report
    Route::post('/fees/payment-report', [FeeApiController::class, 'getPaymentReport'])->name('api.fees.payment-report');
    
    // Full report
    Route::post('/fees/full-report', [FeeApiController::class, 'getFullReport'])->name('api.fees.full-report');
    
    // ==================== STUDENT API ROUTES ====================
    
    // Get students by branch
    Route::post('/students/get-by-branch', [StudentApiController::class, 'getStudentsByBranch'])->name('api.students.get-by-branch');
    
    // Get students by name or GR number
    Route::post('/students/search', [StudentApiController::class, 'searchStudents'])->name('api.students.search');
    
    // Get students for additional attendance
    Route::post('/students/get-for-additional', [StudentApiController::class, 'getStudentsForAdditional'])->name('api.students.get-for-additional');
    
    // Get students for fastrack
    Route::post('/students/get-for-fastrack', [StudentApiController::class, 'getStudentsForFastrack'])->name('api.students.get-for-fastrack');
    
    // Deactive report
    Route::post('/students/deactive-report', [StudentApiController::class, 'getDeactiveReport'])->name('api.students.deactive-report');
    
    // View foundation
    Route::post('/students/view-foundation', [StudentApiController::class, 'getFoundationStudents'])->name('api.students.view-foundation');
    
    // Set status (activate/deactivate)
    Route::post('/students/set-status', [StudentApiController::class, 'setStatus'])->name('api.students.set-status');
    
    // ==================== ORDER API ROUTES ====================
    
    // Get orders list
    Route::post('/orders/get-orders', [OrderApiController::class, 'getOrders'])->name('api.orders.get-orders');
    
    // Mark order as viewed
    Route::post('/orders/mark-viewed', [OrderApiController::class, 'markViewed'])->name('api.orders.mark-viewed');
    
    // Mark order as delivered
    Route::post('/orders/mark-delivered', [OrderApiController::class, 'markDelivered'])->name('api.orders.mark-delivered');
    
    // ==================== EXAM API ROUTES ====================
    
    // Get eligible students for exam
    Route::post('/exam/get-eligible-students', [ExamApiController::class, 'getEligibleStudents'])->name('api.exam.get-eligible-students');
    
    // Set exam eligibility
    Route::post('/exam/set-eligibility', [ExamApiController::class, 'setEligibility'])->name('api.exam.set-eligibility');
    
    // Get exam applied students
    Route::post('/exam/get-applied', [ExamApiController::class, 'getExamApplied'])->name('api.exam.get-applied');
    
    // Exam result report
    Route::post('/exam/result-report', [ExamApiController::class, 'getResultReport'])->name('api.exam.result-report');
    
    // Special exam - set eligibility
    Route::post('/exam/special/set-eligibility', [ExamApiController::class, 'setSpecialEligibility'])->name('api.exam.special.set-eligibility');
    
    // ==================== EVENT API ROUTES ====================
    
    // Get eligible students for event
    Route::post('/event/get-eligible-students', [EventApiController::class, 'getEligibleStudents'])->name('api.event.get-eligible-students');
    
    // Get event applied students
    Route::post('/event/get-applied', [EventApiController::class, 'getEventApplied'])->name('api.event.get-applied');
    
    // ==================== PRODUCT API ROUTES ====================
    
    // Delete product
    Route::post('/products/delete', [OrderApiController::class, 'deleteProduct'])->name('api.products.delete');
    
    // ==================== ATTENDANCE LOG API ROUTES ====================
    
    // Get attendance log
    Route::post('/attendance-log', [AttendanceApiController::class, 'getAttendanceLog'])->name('api.attendance-log');
    
    // ==================== VIEW ATTENDANCE API ROUTES ====================
    
    // View attendance with filters
    Route::post('/view-attendance', [AttendanceApiController::class, 'viewAttendance'])->name('api.view-attendance');
    
    // ==================== EXAM RESULT REPORT API ROUTES ====================
    
    // Get exam result report
    Route::post('/exam-result-report', [ExamApiController::class, 'getExamResultReport'])->name('api.exam-result-report');
    
    // ==================== DEACTIVE REPORT API ROUTES ====================
    
    // Get deactive students report
    Route::post('/deactive-report', [StudentApiController::class, 'getDeactiveReport'])->name('api.deactive-report');
    
    // ==================== SET STATUS API ROUTES ====================
    
    // Set student/fee status (call flag)
    Route::post('/set-status', [StudentApiController::class, 'setStatus'])->name('api.set-status');
    
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
    
});

// Public API routes (if any)
// Route::get('/public-endpoint', [Controller::class, 'method']);

