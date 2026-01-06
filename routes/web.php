<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProductController;
// use App\Http\Controllers\UserController;
use App\Http\Controllers\BeltController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\NewsFeedController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\PageController;

// Public Pages (No authentication required)
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/refund-policy', [PageController::class, 'refund'])->name('refund');
Route::get('/terms-of-service', [PageController::class, 'terms'])->name('terms');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Routes
    // Route::resource('users', UserController::class);

    // Student Routes
    Route::resource('students', StudentController::class);
    Route::patch('students/{student}/deactivate', [StudentController::class, 'deactivate'])->name('students.deactivate');
    Route::post('students/{student}/reset-password', [StudentController::class, 'resetPassword'])->name('students.reset-password');
    Route::get('students/active', [StudentController::class, 'active'])->name('students.active');
    Route::get('students/enquire', [StudentController::class, 'enquire'])->name('students.enquire');
    Route::get('students/deactive-report', [StudentController::class, 'deactiveReport'])->name('students.deactive-report');

    // Branch Routes
    Route::resource('branches', BranchController::class);

    // Belt Routes
    Route::get('belts', [BeltController::class, 'index'])->name('belts.index');
    Route::post('belts/update-exam-fees', [BeltController::class, 'updateExamFees'])->name('belts.update-exam-fees');

    // Fee Routes
    Route::resource('fees', FeeController::class);
    Route::get('fees/enter', [FeeController::class, 'enter'])->name('fees.enter');
    Route::get('fees/enter-old', [FeeController::class, 'enterOld'])->name('fees.enter-old');
    Route::get('fees/enter-exam', [FeeController::class, 'enterExam'])->name('fees.enter-exam');
    Route::get('fees/disable', [FeeController::class, 'disable'])->name('fees.disable');
    Route::get('fees/combined', [FeeController::class, 'combined'])->name('fees.combined');

    // Coupon Routes
    Route::resource('coupons', CouponController::class);

    // Attendance Routes
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/insert', [AttendanceController::class, 'insert'])->name('attendance.insert');
    Route::get('attendance/additional', [AttendanceController::class, 'additional'])->name('attendance.additional');
    Route::get('attendance/log', [AttendanceController::class, 'log'])->name('attendance.log');
    Route::get('attendance/exam', [AttendanceController::class, 'exam'])->name('attendance.exam');
    Route::get('attendance/event', [AttendanceController::class, 'event'])->name('attendance.event');
    Route::get('attendance/view', [AttendanceController::class, 'view'])->name('attendance.view');
    Route::post('attendance/show-form', [AttendanceController::class, 'showForm'])->name('attendance.show-form');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/get-students', [AttendanceController::class, 'getStudents'])->name('attendance.get-students');

    // Product Routes
    Route::resource('products', ProductController::class);

    // Order Routes
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/viewed', [OrderController::class, 'markViewed'])->name('orders.viewed');
    Route::patch('orders/{order}/delivered', [OrderController::class, 'markDelivered'])->name('orders.delivered');

    // Exam Routes
    Route::resource('exams', ExamController::class);
    Route::get('exams/special', [ExamController::class, 'special'])->name('exams.special');
    Route::get('exams/applied', [ExamController::class, 'applied'])->name('exams.applied');
    Route::get('exams/eligible', [ExamController::class, 'eligible'])->name('exams.eligible');
    Route::get('exams/result-report', [ExamController::class, 'resultReport'])->name('exams.result-report');

    // Event Routes
    Route::resource('events', EventController::class);
    Route::get('events/applied', [EventController::class, 'applied'])->name('events.applied');
    Route::get('events/eligible', [EventController::class, 'eligible'])->name('events.eligible');

    // News Feed Routes
    Route::resource('news-feed', NewsFeedController::class);

    // Guide Routes
    Route::resource('guides', GuideController::class);

    // Additional Routes (to be implemented)
    // Route::get('refund', [FeeController::class, 'refund'])->name('refund.index');
    // Route::get('fastrack', [StudentController::class, 'fastrack'])->name('fastrack.index');
    // Route::get('notification', [NotificationController::class, 'index'])->name('notification.index');
    // Route::get('team', [TeamController::class, 'index'])->name('team.index');
    // Route::get('branch-editor', [BranchController::class, 'editor'])->name('branch.editor');
    // Route::get('fix-payment', [FeeController::class, 'fixPayment'])->name('fix-payment.index');
    // Route::get('payment-report', [FeeController::class, 'paymentReport'])->name('payment-report.index');
    // Route::get('full-report', [FeeController::class, 'fullReport'])->name('full-report.index');
    // Route::get('view-foundation', [StudentController::class, 'viewFoundation'])->name('view-foundation.index');
    // Route::get('leave', [AttendanceController::class, 'leave'])->name('leave.index');
    // Route::get('instructor-timetable', [UserController::class, 'timetable'])->name('instructor-timetable.index');
});
