<?php

namespace App\Http\Controllers;

use App\Models\IsoStandard;
use Illuminate\Http\Request;

class GuestAssessmentController extends Controller
{
    /**
     * Show the Guest Audit Wizard
     */
    public function index()
    {
        // Urutan berdasarkan ID biasanya mengikuti urutan input yang logis (Klausul -> Annex)
        $standards = IsoStandard::orderBy('id', 'asc')->get();
        return view('pages.guest.audit', compact('standards'));
    }

    /**
     * AJAX endpoint to get all standards for local processing
     */
    public function getStandards()
    {
        $standards = IsoStandard::all();
        return response()->json($standards);
    }
}
