<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupons = Coupon::where('coupon_id', '!=', 1)->get();
        return view('coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('coupons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'coupon_txt' => 'required|string|unique:coupon,coupon_txt',
            'amount' => 'required|numeric|min:0',
        ]);

        Coupon::create([
            'coupon_txt' => $validated['coupon_txt'],
            'amount' => $validated['amount'],
            'used' => 0,
        ]);

        return redirect()->route('coupons.index')
            ->with('success', 'Coupon added successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('coupons.index')
            ->with('success', 'Coupon deleted successfully.');
    }
}
