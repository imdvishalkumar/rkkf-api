<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display privacy statement page.
     */
    public function privacy()
    {
        return view('pages.privacy');
    }

    /**
     * Display refund policy page.
     */
    public function refund()
    {
        return view('pages.refund');
    }

    /**
     * Display terms of service page.
     */
    public function terms()
    {
        return view('pages.terms');
    }
}
