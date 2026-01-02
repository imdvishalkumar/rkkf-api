<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::all();
        return view('attendance.index', compact('branches'));
    }

    /**
     * Show attendance form for a specific branch and date.
     */
    public function showForm(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branch,branch_id',
            'date' => 'required|date',
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);
        $date = $validated['date'];

        // Get students for this branch
        $students = Student::where('branch_id', $validated['branch_id'])
            ->where('active', 1)
            ->get();

        // Get existing attendance records for this date
        $attendanceRecords = Attendance::where('branch_id', $validated['branch_id'])
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        return view('attendance.form', compact('branch', 'date', 'students', 'attendanceRecords'));
    }

    /**
     * Store or update attendance.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branch,branch_id',
            's_date' => 'required|date',
            'r1' => 'required|array',
            'r1.*' => 'required|in:0,1',
            'attenId' => 'required|array',
            'attenId.*' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $success = true;
            foreach ($validated['r1'] as $index => $attend) {
                $attendanceId = $validated['attenId'][$index];
                $attendance = Attendance::find($attendanceId);
                
                if ($attendance) {
                    $attendance->update(['attend' => $attend]);
                } else {
                    $success = false;
                }
            }

            if ($success) {
                DB::commit();
                return redirect()->route('attendance.index')
                    ->with('success', 'Attendance updated successfully.');
            } else {
                DB::rollBack();
                return back()->with('error', 'Error updating attendance.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating attendance: ' . $e->getMessage());
        }
    }

    /**
     * Get students for attendance (AJAX).
     */
    public function getStudents(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branch,branch_id',
            'date' => 'required|date',
        ]);

        $students = Student::where('branch_id', $validated['branch_id'])
            ->where('active', 1)
            ->get();

        // Create or get attendance records
        $attendanceRecords = [];
        foreach ($students as $student) {
            $attendance = Attendance::firstOrCreate(
                [
                    'student_id' => $student->student_id,
                    'branch_id' => $validated['branch_id'],
                    'date' => $validated['date'],
                ],
                ['attend' => 0]
            );
            $attendanceRecords[] = $attendance;
        }

        return response()->json([
            'students' => $students,
            'attendance' => $attendanceRecords,
        ]);
    }
}
