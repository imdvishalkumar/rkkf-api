<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::all();
        return view('branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::all(); // For transfer dropdown
        return view('branches.create', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->has('transfer')) {
            // Transfer branch
            return $this->transferBranch($request);
        }

        // Add new branch
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255|unique:branch,name',
            'branch_fees' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'late' => 'nullable|numeric|min:0',
            'days' => 'required|array|min:1',
        ]);

        $daysStr = implode(',', $validated['days']);

        Branch::create([
            'name' => $validated['branch_name'],
            'fees' => $validated['branch_fees'],
            'discount' => $validated['discount'] ?? 0,
            'late' => $validated['late'] ?? 0,
            'days' => $daysStr,
        ]);

        return redirect()->route('branches.index')
            ->with('success', 'Branch added successfully.');
    }

    /**
     * Transfer students from one branch to another.
     */
    private function transferBranch(Request $request)
    {
        $validated = $request->validate([
            'from_branch_id' => 'required|exists:branch,branch_id',
            'to_branch_id' => 'required|exists:branch,branch_id|different:from_branch_id',
        ]);

        $studentCount = Student::where('branch_id', $validated['from_branch_id'])->count();

        Student::where('branch_id', $validated['from_branch_id'])
            ->update(['branch_id' => $validated['to_branch_id']]);

        return redirect()->route('branches.index')
            ->with('success', "{$studentCount} students transferred successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {
        $branch->load('students');
        return view('branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branch,name,' . $branch->branch_id . ',branch_id',
            'fees' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'late' => 'nullable|numeric|min:0',
            'days' => 'required|array|min:1',
        ]);

        $daysStr = implode(',', $validated['days']);

        $branch->update([
            'name' => $validated['name'],
            'fees' => $validated['fees'],
            'discount' => $validated['discount'] ?? 0,
            'late' => $validated['late'] ?? 0,
            'days' => $daysStr,
        ]);

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }
}
