<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fee;
use App\Models\Branch;
use App\Models\Student;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class FeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Fee::with(['student.branch', 'coupon'])
            ->join('students as s', 'fees.student_id', '=', 's.student_id')
            ->where('s.active', 1)
            ->select('fees.*');

        // Apply filters
        if ($request->has('branch_id') && $request->branch_id != 0) {
            $query->where('s.branch_id', $request->branch_id);
        }

        if ($request->has('startdate') && $request->has('enddate')) {
            $query->whereBetween('fees.date', [$request->startdate, $request->enddate]);
        }

        if ($request->has('paid_pending')) {
            // Filter logic for paid/pending
        }

        $fees = $query->get();
        $branches = Branch::all();

        return view('fees.index', compact('fees', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::where('active', 1)->get();
        $coupons = Coupon::where('active', 1)->get();
        return view('fees.create', compact('students', 'coupons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'months' => 'required|integer|between:1,12',
            'year' => 'required|integer',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'coupon_id' => 'nullable|exists:coupon,coupon_id',
            'mode' => 'required|in:cash,online',
            'remarks' => 'nullable|string',
        ]);

        Fee::create([
            'student_id' => $validated['student_id'],
            'months' => $validated['months'],
            'year' => $validated['year'],
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'coupon_id' => $validated['coupon_id'] ?? 1,
            'additional' => 0,
            'disabled' => 0,
            'mode' => $validated['mode'],
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('fees.index')
            ->with('success', 'Fee added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Fee $fee)
    {
        $fee->load(['student.branch', 'coupon']);
        return view('fees.show', compact('fee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fee $fee)
    {
        $students = Student::where('active', 1)->get();
        $coupons = Coupon::where('active', 1)->get();
        return view('fees.edit', compact('fee', 'students', 'coupons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fee $fee)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'disabled' => 'boolean',
        ]);

        $fee->update($validated);

        return redirect()->route('fees.index')
            ->with('success', 'Fee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fee $fee)
    {
        $fee->delete();
        return redirect()->route('fees.index')
            ->with('success', 'Fee deleted successfully.');
    }
}
