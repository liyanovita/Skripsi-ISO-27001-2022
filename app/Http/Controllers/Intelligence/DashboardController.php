<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSession;
use App\Services\Intelligence\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request): View
    {
        $data = $this->dashboardService->getDashboardData(auth()->id());

        return view('pages.dashboard', $data);
    }
}
