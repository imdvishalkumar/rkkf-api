<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StudentService;
use App\Services\BranchService;
use App\Services\BeltService;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\Student;
use App\Models\Branch;
use App\Models\Belt;

class StudentController extends Controller
{
    protected $studentService;
    protected $branchService;
    protected $beltService;

    public function __construct(
        StudentService $studentService,
        BranchService $branchService,
        BeltService $beltService
    ) {
        $this->studentService = $studentService;
        $this->branchService = $branchService;
        $this->beltService = $beltService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [];
        
        if ($request->has('branch_id') && $request->branch_id != 0) {
            $filters['branch_id'] = $request->branch_id;
        }

        if ($request->has('belt_id') && $request->belt_id != 0) {
            $filters['belt_id'] = $request->belt_id;
        }

        if ($request->has('startdate') && $request->has('enddate')) {
            $filters['start_date'] = $request->startdate;
            $filters['end_date'] = $request->enddate;
        }

        $filters['active'] = 1;
        $students = $this->studentService->getAllStudents($filters);
        $branches = $this->branchService->getAllBranches();
        $belts = $this->beltService->getAllBelts();

        return view('students.index', compact('students', 'branches', 'belts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = $this->branchService->getAllBranches();
        $belts = $this->beltService->getAllBelts();
        return view('students.create', compact('branches', 'belts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Map form fields to model fields
            $studentData = [
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'gender' => $data['gender'],
                'email' => $data['email'],
                'password' => $data['selfno'] ?? $data['smno'] ?? null, // Service will hash it
                'belt_id' => $data['belt_id'] ?? $data['belt'] ?? null,
                'dadno' => $data['dadno'] ?? $data['dmno'] ?? null,
                'dadwp' => $data['dadwp'] ?? $data['dwno'] ?? null,
                'momno' => $data['momno'] ?? $data['mmno'] ?? null,
                'momwp' => $data['momwp'] ?? $data['mwno'] ?? null,
                'selfno' => $data['selfno'] ?? $data['smno'] ?? null,
                'selfwp' => $data['selfwp'] ?? $data['swno'] ?? null,
                'dob' => $data['dob'],
                'doj' => $data['doj'],
                'address' => $data['address'] ?? null,
                'branch_id' => $data['branch_id'],
                'pincode' => $data['pincode'] ?? null,
                'active' => 1,
            ];

            // Add fees data if provided
            if (isset($data['fees']) && isset($data['months'])) {
                $studentData['fees'] = ['amount' => $data['fees']];
                $studentData['months'] = $data['months'];
            }

            $result = $this->studentService->createStudent($studentData);
            
            return redirect()->route('students.index')
                ->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error adding student: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $student = $this->studentService->getStudentById($id);
            return view('students.show', compact('student'));
        } catch (\Exception $e) {
            return redirect()->route('students.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        try {
            $student = $this->studentService->getStudentById($id);
            $branches = $this->branchService->getAllBranches();
            $belts = $this->beltService->getAllBelts();
            return view('students.edit', compact('student', 'branches', 'belts'));
        } catch (\Exception $e) {
            return redirect()->route('students.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, int $id)
    {
        try {
            $result = $this->studentService->updateStudent($id, $request->validated());
            return redirect()->route('students.index')
                ->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error updating student: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $this->studentService->deleteStudent($id);
            return redirect()->route('students.index')
                ->with('success', 'Student deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('students.index')
                ->with('error', 'Error deleting student: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate a student.
     */
    public function deactivate(int $id)
    {
        try {
            $this->studentService->deactivateStudent($id);
            return redirect()->route('students.index')
                ->with('success', 'Student deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('students.index')
                ->with('error', 'Error deactivating student: ' . $e->getMessage());
        }
    }

    /**
     * Reset student password.
     */
    public function resetPassword(int $id)
    {
        try {
            $password = $this->studentService->resetPassword($id);
            return redirect()->route('students.index')
                ->with('success', "Student password reset successfully to: {$password}");
        } catch (\Exception $e) {
            return redirect()->route('students.index')
                ->with('error', 'Error resetting password: ' . $e->getMessage());
        }
    }
}
