<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Services\Intelligence\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}


    public function strategic(Request $request): View
    {
        $data = $this->analyticsService->getStrategicData(
            auth()->id(),
            $request->get('session_id') ? (int) $request->get('session_id') : null
        );

        return view('pages.intelligence.strategic', $data);
    }
}
