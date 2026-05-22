<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function dailyReport()
    {
        return view('admin.reports.daily-report');
    }
}
