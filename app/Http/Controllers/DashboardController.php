<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard)
    {
    }

    public function __invoke(Request $request): View
    {
        return view('dashboard.index', [
            'summary' => $this->dashboard->summary($request->user()),
        ]);
    }
}
