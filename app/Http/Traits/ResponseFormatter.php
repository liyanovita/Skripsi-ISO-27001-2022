<?php

namespace App\Http\Traits;

use Illuminate\Http\RedirectResponse;

/**
 * Response Formatter Trait
 *
 * Provides standardized redirect response methods for controllers.
 */
trait ResponseFormatter
{
    /**
     * Return a success redirect response with flash message
     */
    protected function successRedirect(
        string $route,
        string $message = null,
        array $params = []
    ): RedirectResponse {
        $message = $message ?? __('Operation completed successfully');
        return redirect()->route($route, $params)
            ->with('success', $message);
    }

    /**
     * Return an error redirect response with flash message
     */
    protected function errorRedirect(string $message = null): RedirectResponse
    {
        $message = $message ?? __('An error occurred');
        return redirect()->back()
            ->with('error', $message);
    }
}
