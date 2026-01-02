<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Student;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $productCount = Product::count();
        $branchCount = Branch::count();
        $studentCount = Student::where('active', 1)
            ->where('belt_id', '<', 14)
            ->count();
        $highBeltStudents = Student::where('active', 1)
            ->where('belt_id', '>=', 14)
            ->count();
        $insCount = User::where('role', 2)->count();
        $unSeenOrderCount = 0; // TODO: Implement Order model
        $enquireCount = 0; // TODO: Implement Enquire model

        return view('dashboard.index', compact(
            'productCount',
            'branchCount',
            'studentCount',
            'highBeltStudents',
            'insCount',
            'unSeenOrderCount',
            'enquireCount'
        ));
    }
}
