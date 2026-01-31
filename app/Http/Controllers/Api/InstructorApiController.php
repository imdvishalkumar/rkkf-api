<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class InstructorApiController extends Controller
{
    protected $examService;

    public function __construct(\App\Services\ExamService $examService)
    {
        $this->examService = $examService;
    }


    /**
     * Get all branches
     * GET /api/instructor/branches
     */
    public function getAllBranches()
    {
        try {
            $branches = \App\Models\Branch::select('branch_id', 'name', 'days', 'address', 'city')
                ->orderBy('name', 'asc')
                ->get();

            return ApiResponseHelper::success([
                'data' => $branches,
            ], 'Branches retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Insert exam attendance with certificate generation
     * POST /api/instructor/exams/attendance
     */
    public function insertExamAttendance(Request $request)
    {
        try {
            $request->validate([
                'exam_id' => 'required|integer|exists:exam,exam_id',
                'attendanceArray' => 'required', // Allow string or array
            ]);

            $user = Auth::user();
            $examId = $request->input('exam_id');
            $rawValue = $request->input('attendanceArray');

            // Handle both JSON string and native array
            $decodedArray = is_array($rawValue) ? $rawValue : json_decode($rawValue, true);

            if (!is_array($decodedArray)) {
                return ApiResponseHelper::error('Invalid attendance array format', 422);
            }

            $result = $this->examService->markAttendance($examId, $decodedArray, $user->user_id);

            return ApiResponseHelper::success($result, $result['message']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Search students by name or GR number
     * GET /api/instructor/students/search?name=john
     */
    public function searchStudents(Request $request)
    {
        try {
            $name = $request->query('name', '');
            $name = trim($name);

            $students = \App\Models\Student::with(['branch', 'belt']) // Eager load relations
                ->where('active', 1)
                ->where(function ($query) use ($name) {
                    $query->where('student_id', 'like', $name . '%')
                        ->orWhere(DB::raw("CONCAT(firstname, ' ', lastname)"), 'like', '%' . $name . '%');
                })
                ->limit(50)
                ->get();

            if ($students->isEmpty()) {
                return ApiResponseHelper::success([
                    'data' => [],
                ], 'No Student found!');
            }

            return ApiResponseHelper::success([
                'data' => \App\Http\Resources\StudentResource::collection($students),
            ], 'Students retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get attendance count for additional attendance
     * POST /api/instructor/attendance/count
     */
    public function getAttendanceCount(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|integer',
                'date' => 'required|date',
                'branch_id' => 'required|integer',
            ]);

            $studentId = $request->input('student_id');
            $date = date('Y-m-d', strtotime($request->input('date')));
            $branchId = $request->input('branch_id');

            $attendance = DB::table('attendance')
                ->where('date', $date)
                ->where('student_id', $studentId)
                ->get();

            $presentCount = 0;
            $eventCount = 0;

            foreach ($attendance as $record) {
                if ($record->attend == 'P') {
                    $presentCount++;
                } elseif ($record->attend == 'E') {
                    $eventCount++;
                }
            }

            return ApiResponseHelper::success([
                'present_count' => $presentCount,
                'event_count' => $eventCount,
                'done' => 1,
            ], 'Attendance count retrieved');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Insert fastrack attendance
     * POST /api/instructor/fastrack/attendance
     */
    public function insertFastrackAttendance(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|integer',
                'hours' => 'required|numeric',
                'branch_id' => 'required|integer',
            ]);

            $user = Auth::user();
            $studentId = $request->input('student_id');
            $hours = $request->input('hours');
            $branchId = $request->input('branch_id');
            $date = date('Y-m-d');

            // Insert fastrack attendance
            DB::table('fastrack_attendance')->insert([
                'student_id' => $studentId,
                'hours' => $hours,
                'date' => $date,
                'branch_id' => $branchId,
                'user_id' => $user->user_id,
            ]);

            return ApiResponseHelper::success([
                'saved' => 1,
            ], 'Attendance Submitted.');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get events for attendance
     * GET /api/instructor/events/for-attendance
     */
    public function getEventsForAttendance()
    {
        try {
            $events = DB::table('event')
                ->orderBy('from_date', 'desc')
                ->limit(5)
                ->get();

            if ($events->isEmpty()) {
                return ApiResponseHelper::error('No event Found!', 422);
            }

            return ApiResponseHelper::success([
                'data' => $events,
            ], 'Events retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get students enrolled for an event
     * GET /api/instructor/events/{id}/students
     */
    public function getStudentsForEvent($id)
    {
        try {
            if (!is_numeric($id)) {
                return ApiResponseHelper::error('Invalid ID!', 422);
            }

            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('event_fees as ef', 'ef.student_id', '=', 's.student_id')
                ->where('s.active', 1)
                ->where('ef.status', 1)
                ->where('ef.event_id', $id)
                ->select('s.*', 'br.name as branch_name')
                ->get();

            if ($students->isEmpty()) {
                return ApiResponseHelper::success([
                    'data' => [],
                ], 'No Student found in Event!');
            }

            return ApiResponseHelper::success([
                'data' => $students,
            ], 'Students retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Insert event attendance
     * POST /api/instructor/events/attendance
     */
    public function insertEventAttendance(Request $request)
    {
        try {
            $request->validate([
                'event_id' => 'required|integer',
                'attendanceArray' => 'required|string',
            ]);

            $user = Auth::user();
            $eventId = $request->input('event_id');
            $attenArray = $request->input('attendanceArray');

            // Check if attendance already exists
            $existing = DB::table('event_attendance')
                ->where('event_id', $eventId)
                ->exists();

            if ($existing) {
                return ApiResponseHelper::success([
                    'saved' => 0,
                ], 'Attendance already exists!');
            }

            $decodedArray = json_decode($attenArray, true);
            $presentArr = [];
            $absentArr = [];
            $leaveArr = [];

            foreach ($decodedArray as $value) {
                if (isset($value['present_student_id'])) {
                    $presentArr[] = $value['present_student_id'];
                }
                if (isset($value['absent_student_id'])) {
                    $absentArr[] = $value['absent_student_id'];
                }
                if (isset($value['leave_student_id'])) {
                    $leaveArr[] = $value['leave_student_id'];
                }
            }

            // Insert present students
            foreach ($presentArr as $studentId) {
                DB::table('event_attendance')->insert([
                    'event_id' => $eventId,
                    'student_id' => $studentId,
                    'attend' => 'P',
                    'user_id' => $user->user_id,
                ]);
            }

            // Insert absent students
            foreach ($absentArr as $studentId) {
                DB::table('event_attendance')->insert([
                    'event_id' => $eventId,
                    'student_id' => $studentId,
                    'attend' => 'A',
                    'user_id' => $user->user_id,
                ]);
            }

            // Insert leave students
            foreach ($leaveArr as $studentId) {
                DB::table('event_attendance')->insert([
                    'event_id' => $eventId,
                    'student_id' => $studentId,
                    'attend' => 'F',
                    'user_id' => $user->user_id,
                ]);
            }

            return ApiResponseHelper::success([
                'saved' => 1,
            ], 'Attendance Submitted.');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get exams for attendance (today's exams)
     * GET /api/instructor/exams/for-attendance
     */
    public function getExamsForAttendance()
    {
        try {
            $exams = DB::table('exam')
                ->whereDate('date', now()->format('Y-m-d'))
                ->orderBy('date', 'asc')
                ->get();

            if ($exams->isEmpty()) {
                return ApiResponseHelper::error('No Exam Found!', 422);
            }

            return ApiResponseHelper::success([
                'data' => $exams,
            ], 'Exams retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get students enrolled for an exam
     * GET /api/instructor/exams/{id}/students
     */
    public function getStudentsForExam($id)
    {
        try {
            if (!is_numeric($id)) {
                return ApiResponseHelper::error('Invalid ID!', 422);
            }

            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('exam_fees as ef', 'ef.student_id', '=', 's.student_id')
                ->where('s.active', 1)
                ->where('ef.status', 1)
                ->where('ef.exam_id', $id)
                ->select('s.*', 'br.name as branch_name')
                ->get();

            if ($students->isEmpty()) {
                return ApiResponseHelper::success([
                    'data' => [],
                ], 'No Student found in Exam!');
            }

            return ApiResponseHelper::success([
                'data' => $students,
            ], 'Students retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Insert exam attendance with certificate generation
     * POST /api/instructor/exams/attendance
     */



    /**
     * Get all students list for instructor (Student Details screen)
     * GET /api/instructor/students?branch_id=1&search=john
     * 
     * Features:
     * - Filter by branch
     * - Search by name or student ID
     * - Includes branch name and belt details
     */
    public function getAllStudents(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'nullable|integer|exists:branch,branch_id',
                'search' => 'nullable|string|max:100',
            ]);

            $branchId = $request->input('branch_id');
            $search = $request->input('search', '');
            $search = trim($search);

            $query = \App\Models\Student::with(['branch', 'belt'])
                ->where('active', 1);

            // Filter by branch if provided
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            // Search by name or student ID
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('student_id', 'like', $search . '%')
                        ->orWhere(DB::raw("CONCAT(firstname, ' ', lastname)"), 'like', '%' . $search . '%');
                });
            }

            $students = $query->orderBy('firstname', 'asc')
                ->limit(100)
                ->get();

            $data = $students->map(function ($student) {
                return [
                    'student_id' => $student->student_id,
                    'name' => $student->firstname . ' ' . $student->lastname,
                    'firstname' => $student->firstname,
                    'lastname' => $student->lastname,
                    'branch_id' => $student->branch_id,
                    'branch_name' => $student->branch?->name,
                    'belt_id' => $student->belt_id,
                    'belt_name' => $student->belt?->name,
                    'profile_img' => $student->profile_img,
                    'active' => $student->active,
                ];
            });

            return ApiResponseHelper::success([
                'data' => $data,
                'total' => $data->count(),
            ], 'Students retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get detailed student profile for instructor view
     * GET /api/instructor/students/{id}/profile
     * 
     * Returns complete student information including:
     * - Personal Info (email, DOB, DOJ, address, pincode)
     * - Contact info (self, father, mother numbers)
     * - Branch and Belt details
     * - Total attendance count
     */
    public function getStudentProfile($id)
    {
        try {
            if (!is_numeric($id)) {
                return ApiResponseHelper::error('Invalid Student ID!', 422);
            }

            $student = \App\Models\Student::with(['branch', 'belt'])
                ->where('student_id', $id)
                ->first();

            if (!$student) {
                return ApiResponseHelper::error('Student not found!', 404);
            }

            // Get attendance count
            $attendanceCount = DB::table('attendance')
                ->where('student_id', $id)
                ->where('attend', 'P')
                ->count();

            // Map Gender
            $genderMap = [
                0 => 'Other',
                1 => 'Male',
                2 => 'Female',
            ];
            $gender = $genderMap[$student->gender] ?? 'Other';

            // Prepare profile image URL
            $profileImgUrl = asset('images/default-avatar.png');
            if ($student->profile_img && $student->profile_img !== 'default.png') {
                $profileImgUrl = asset('storage/profile_images/' . $student->profile_img);
            }

            $data = [
                'student_id' => $student->student_id,
                'name' => $student->firstname . ' ' . $student->lastname,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'role' => 'Student',
                'profile_img_url' => $profileImgUrl,

                // Personal Info Tab
                'personal_info' => [
                    'email' => $student->email,
                    'joining_date' => $student->doj ? $student->doj->format('d-m-Y') : null,
                    'date_of_birth' => $student->dob ? $student->dob->format('d-m-Y') : null,
                    'address' => $student->address,
                    'pincode' => $student->pincode,
                    'gender' => $gender,
                    'std' => $student->std,
                ],

                // Branch and Belt Info
                'branch_id' => $student->branch_id,
                'branch_name' => $student->branch?->name,
                'belt_id' => $student->belt_id,
                'belt_name' => $student->belt?->name,

                // Attendance
                'attendance_count' => $attendanceCount,
                'dib' => 'N/A', // Days in Branch - can calculate if needed

                // Contact Tab
                'contact' => [
                    'self_number' => $student->selfno,
                    'self_whatsapp' => $student->selfwp,
                    'father_number' => $student->dadno,
                    'father_whatsapp' => $student->dadwp,
                    'mother_number' => $student->momno,
                    'mother_whatsapp' => $student->momwp,
                ],

                'active' => (bool) $student->active,
                'gr_no' => $student->gr_no,
            ];

            return ApiResponseHelper::success([
                'data' => $data,
            ], 'Student profile retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get students list for Additional Attendance screen
     * GET /api/instructor/additional-attendance/students?branch_id=1&search=john
     * 
     * Returns students with their attendance count for the selected date
     */
    public function getStudentsForAdditionalAttendance(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'nullable|integer|exists:branch,branch_id',
                'search' => 'nullable|string|max:100',
            ]);

            $branchId = $request->input('branch_id');
            $search = $request->input('search', '');
            $search = trim($search);

            $query = \App\Models\Student::with(['branch'])
                ->where('active', 1)
                ->whereNotIn('student_id', function ($q) {
                    $q->select('student_id')->from('fastrack');
                });

            // Filter by branch if provided
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            // Search by name or student ID
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('student_id', 'like', $search . '%')
                        ->orWhere(DB::raw("CONCAT(firstname, ' ', lastname)"), 'like', '%' . $search . '%');
                });
            }

            $students = $query->orderBy('firstname', 'asc')
                ->limit(100)
                ->get();

            $data = $students->map(function ($student) {
                // Get today's attendance count for this student
                $attendCount = DB::table('attendance')
                    ->where('student_id', $student->student_id)
                    ->where('date', now()->format('Y-m-d'))
                    ->where('attend', 'P')
                    ->count();

                return [
                    'student_id' => $student->student_id,
                    'name' => $student->firstname . ' ' . $student->lastname,
                    'firstname' => $student->firstname,
                    'lastname' => $student->lastname,
                    'branch_id' => $student->branch_id,
                    'branch_name' => $student->branch?->name,
                    'attend_count' => $attendCount,
                    'profile_img' => $student->profile_img,
                ];
            });

            return ApiResponseHelper::success([
                'data' => $data,
                'total' => $data->count(),
            ], 'Students retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get students list for Fastrack Attendance screen
     * GET /api/instructor/fastrack/students?branch_id=1&start_date=2024-01-01&end_date=2024-01-31&search=john
     * 
     * Returns fastrack students with optional date range filter
     */
    public function getStudentsForFastrackAttendance(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'nullable|integer|exists:branch,branch_id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'search' => 'nullable|string|max:100',
            ]);

            $branchId = $request->input('branch_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $search = $request->input('search', '');
            $search = trim($search);

            // Get fastrack students
            $query = DB::table('students as s')
                ->join('fastrack as f', 's.student_id', '=', 'f.student_id')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.active', 1);

            // Filter by branch if provided
            if ($branchId) {
                $query->where('s.branch_id', $branchId);
            }

            // Search by name or student ID
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('s.student_id', 'like', $search . '%')
                        ->orWhere(DB::raw("CONCAT(s.firstname, ' ', s.lastname)"), 'like', '%' . $search . '%');
                });
            }

            $students = $query->select(
                's.student_id',
                's.firstname',
                's.lastname',
                's.branch_id',
                's.profile_img',
                'br.name as branch_name'
            )
                ->orderBy('s.firstname', 'asc')
                ->limit(100)
                ->get();

            // Get fastrack hours for each student if date range is provided
            $data = $students->map(function ($student) use ($startDate, $endDate) {
                $hoursQuery = DB::table('fastrack_attendance')
                    ->where('student_id', $student->student_id);

                if ($startDate && $endDate) {
                    $hoursQuery->whereBetween('date', [$startDate, $endDate]);
                }

                $totalHours = $hoursQuery->sum('hours');

                return [
                    'student_id' => $student->student_id,
                    'name' => $student->firstname . ' ' . $student->lastname,
                    'firstname' => $student->firstname,
                    'lastname' => $student->lastname,
                    'branch_id' => $student->branch_id,
                    'branch_name' => $student->branch_name,
                    'profile_img' => $student->profile_img,
                    'total_hours' => (float) $totalHours,
                ];
            });

            return ApiResponseHelper::success([
                'data' => $data,
                'total' => $data->count(),
            ], 'Fastrack students retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Update student contact information (for instructor)
     * PUT /api/instructor/students/{id}/contact
     */
    public function updateStudentContact(Request $request, $id)
    {
        try {
            if (!is_numeric($id)) {
                return ApiResponseHelper::error('Invalid Student ID!', 422);
            }

            $request->validate([
                'selfno' => 'nullable|string|max:15',
                'selfwp' => 'nullable|string|max:15',
                'dadno' => 'nullable|string|max:15',
                'dadwp' => 'nullable|string|max:15',
                'momno' => 'nullable|string|max:15',
                'momwp' => 'nullable|string|max:15',
            ]);

            $student = \App\Models\Student::find($id);

            if (!$student) {
                return ApiResponseHelper::error('Student not found!', 404);
            }

            $student->update($request->only([
                'selfno',
                'selfwp',
                'dadno',
                'dadwp',
                'momno',
                'momwp'
            ]));

            return ApiResponseHelper::success([
                'updated' => 1,
            ], 'Student contact updated successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }
}
