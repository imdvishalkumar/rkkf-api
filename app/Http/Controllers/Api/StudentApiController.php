<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StudentService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Student\UpdateStudentProfileRequest;
use Exception;

class StudentApiController extends Controller
{
    protected $studentService;
    protected $attendanceService;

    // Inject AttendanceService alongside StudentService
    public function __construct(StudentService $studentService, \App\Services\AttendanceService $attendanceService)
    {
        $this->studentService = $studentService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Get students by branch
     * GET /api/students/get-by-branch?branch_id=1&belt_id=2&start_date=2024-01-01&end_date=2024-12-31
     */
    public function getStudentsByBranch(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
                'belt_id' => 'nullable|integer|exists:belt,belt_id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $filters = [
                'branch_id' => $request->input('branch_id'),
            ];

            if ($request->has('belt_id')) {
                $filters['belt_id'] = $request->input('belt_id');
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $filters['start_date'] = $request->input('start_date');
                $filters['end_date'] = $request->input('end_date');
            }

            $students = $this->studentService->getAllStudents($filters);

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Search students by GR number or name
     * GET /api/students/search?grno=101
     */
    public function searchStudents(Request $request)
    {
        try {
            $request->validate([
                'grno' => 'required|string',
            ]);

            $grno = $request->input('grno');

            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('belt as b', 's.belt_id', '=', 'b.belt_id')
                ->where('s.student_id', 'like', $grno . '%')
                ->orWhere(DB::raw('CONCAT(s.firstname, " ", s.lastname)'), 'like', '%' . $grno . '%')
                ->select(
                    's.*',
                    'br.name as branch_name',
                    'b.name as belt_name'
                )
                ->get();

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get students for additional attendance
     * GET /api/students/get-for-additional?grno=101
     */
    public function getStudentsForAdditional(Request $request)
    {
        try {
            $request->validate([
                'grno' => 'required|string',
            ]);

            $grno = $request->input('grno');

            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.student_id', 'like', $grno . '%')
                ->orWhere(DB::raw('CONCAT(s.firstname, " ", s.lastname)'), 'like', '%' . $grno . '%')
                ->where('s.active', 1)
                ->select(
                    's.student_id',
                    's.firstname',
                    's.lastname',
                    'br.name as branch_name'
                )
                ->get();

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get students for fastrack
     * GET /api/students/get-for-fastrack?grno=101
     */
    public function getStudentsForFastrack(Request $request)
    {
        try {
            $request->validate([
                'grno' => 'required|string',
            ]);

            $grno = $request->input('grno');

            $students = DB::table('students as s')
                ->join('fastrack as f', 's.student_id', '=', 'f.student_id')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.student_id', 'like', $grno . '%')
                ->orWhere(DB::raw('CONCAT(s.firstname, " ", s.lastname)'), 'like', '%' . $grno . '%')
                ->where('s.active', 1)
                ->select(
                    's.*',
                    'br.name as branch_name'
                )
                ->get();

            return ApiResponseHelper::success($students, 'Fastrack students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Deactive report
     * GET /api/students/deactive-report?branch_id=1&start_date=2024-01-01&end_date=2024-12-31
     */
    public function getDeactiveReport(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $branchId = $request->input('branch_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.branch_id', $branchId)
                ->where('s.active', 0)
                ->whereBetween('s.doj', [$startDate, $endDate])
                ->select(
                    's.*',
                    'br.name as branch_name'
                )
                ->get();

            return ApiResponseHelper::success($students, 'Deactive students report retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * View foundation students
     * GET /api/students/view-foundation?branch_id=1
     */
    public function getFoundationStudents(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
            ]);

            $branchId = $request->input('branch_id');

            // Foundation students are typically those with belt_id = 1 (white belt)
            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('belt as b', 's.belt_id', '=', 'b.belt_id')
                ->where('s.branch_id', $branchId)
                ->where('s.belt_id', 1) // Assuming belt_id 1 is foundation/white belt
                ->where('s.active', 1)
                ->select(
                    's.*',
                    'br.name as branch_name',
                    'b.name as belt_name'
                )
                ->get();

            return ApiResponseHelper::success($students, 'Foundation students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Set status (activate/deactivate)
     * POST /api/students/set-status
     */
    public function setStatus(Request $request)
    {
        try {
            $request->validate([
                'stuId' => 'required|integer|exists:students,student_id',
                'from' => 'required|integer|in:0,1', // 0 = deactivate, 1 = activate
            ]);

            $studentId = $request->input('stuId');
            $from = $request->input('from');

            if ($from == 1) {
                $this->studentService->activateStudent($studentId);
                $message = 'Student activated successfully';
            } else {
                $this->studentService->deactivateStudent($studentId);
                $message = 'Student deactivated successfully';
            }

            return ApiResponseHelper::success(null, $message);
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get student profile
     * GET /api/students/profile
     */
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Get student associated with user
            $student = \App\Models\Student::where('email', $user->email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Student profile not found', 404);
            }

            // Add full URL for profile image
            if ($student->profile_img && $student->profile_img !== 'default.png') {
                $student->profile_img_url = Storage::disk('public')->url('profile_images/' . $student->profile_img);
            } else {
                $student->profile_img_url = asset('images/default-avatar.png');
            }

            return ApiResponseHelper::success($student, 'Profile retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Update student profile
     * PUT /api/students/profile
     */
    public function updateProfile(UpdateStudentProfileRequest $request)
    {
        try {
            $user = $request->user();

            // Get student associated with user
            // Assuming user has 'email' which links to 'student'
            $student = \App\Models\Student::where('email', $user->email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Student profile not found', 404);
            }

            $data = $request->validated();

            // Handle profile image upload
            if ($request->hasFile('profile_img')) {
                // Delete old image if it exists and is not default
                if ($student->profile_img && $student->profile_img !== 'default.png') {
                    $oldPath = 'profile_images/' . $student->profile_img;
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $file = $request->file('profile_img');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('profile_images', $fileName, 'public');
                $data['profile_img'] = $fileName;
            }

            // Update using service
            $this->studentService->updateStudent($student->student_id, $data);

            // Get updated student with fresh data
            $student->refresh();

            // Add full URL for profile image
            if ($student->profile_img && $student->profile_img !== 'default.png') {
                $student->profile_img_url = Storage::disk('public')->url('profile_images/' . $student->profile_img);
            } else {
                // Fallback to default if needed
                $student->profile_img_url = asset('images/default-avatar.png');
            }

            return ApiResponseHelper::success($student, 'Profile updated successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get student attendance overview
     * GET /api/students/attendance
     */
    public function getAttendanceOverview(Request $request)
    {
        try {
            $request->validate([
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date',
            ]);

            // Securely get student from authenticated user
            $student = $request->user()->student;

            if (!$student) {
                return ApiResponseHelper::error('Student profile not associated with this user', 404);
            }

            // Determine date range (default to current month if not provided)
            $startDate = $request->input('from_date') ?? date('Y-m-01');
            $endDate = $request->input('to_date') ?? date('Y-m-t');

            // Call service
            $data = $this->attendanceService->getStudentAttendanceOverview($student->student_id, $startDate, $endDate);

            // Prepare response structure matching Figma
            $response = [
                'student_info' => [
                    'name' => $student->firstname . ' ' . $student->lastname,
                    'gr_no' => $student->gr_no,
                    'student_id' => $student->student_id,
                ],
                'date_range' => [
                    'from' => $startDate,
                    'to' => $endDate
                ],
                'overview' => $data['overview']
            ];

            return ApiResponseHelper::success($response, 'Attendance overview retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
}
