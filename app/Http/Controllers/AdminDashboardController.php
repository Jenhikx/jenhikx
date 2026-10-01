<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboard)
    {
        $period = in_array($request->query('period'), ['this_month', 'last_month', 'this_year', 'all_time'])
            ? $request->query('period')
            : 'this_month';

        $data = $dashboard->build($period);

        return view('admin.dashboard', array_merge($data, ['period' => $period]));
    }
}