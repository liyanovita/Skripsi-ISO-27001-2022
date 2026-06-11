<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Switch the application language.
     */
    public function switchLang($lang)
    {
        if (in_array($lang, ['en', 'id'])) {
            session()->put('locale', $lang);
        }

        // Use route-based redirect to prevent open redirect via Referer header
        $previous = url()->previous();
        $appUrl = config('app.url');

        if (str_starts_with($previous, $appUrl)) {
            return redirect($previous);
        }

        return redirect()->route('dashboard');
    }
}
