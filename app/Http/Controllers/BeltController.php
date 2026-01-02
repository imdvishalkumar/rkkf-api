<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Belt;
use Illuminate\Support\Facades\DB;

class BeltController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $belts = Belt::all();
        return view('belts.index', compact('belts'));
    }

    /**
     * Update exam fees for belts.
     */
    public function updateExamFees(Request $request)
    {
        $validated = $request->validate([
            'belt_id' => 'required|array',
            'belt_id.*' => 'required|exists:belt,belt_id',
            'exam_fees' => 'required|array',
            'exam_fees.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['belt_id'] as $index => $beltId) {
                Belt::where('belt_id', $beltId)
                    ->update(['exam_fees' => $validated['exam_fees'][$index]]);
            }

            DB::commit();
            return redirect()->route('belts.index')
                ->with('success', 'Belt exam fees updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating belt exam fees: ' . $e->getMessage());
        }
    }
}
