<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Governance\UpdateProfileRequest;
use App\Http\Requests\Governance\UpdatePasswordRequest;
use App\Http\Traits\ResponseFormatter;
use App\Services\Governance\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use ResponseFormatter;

    public function __construct(
        protected ProfileService $profileService
    ) {}

    public function edit(): View
    {
        $data = $this->profileService->getProfileData(auth()->user());
        return view('profile.edit', $data);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        try {
            $this->profileService->updateProfile($request->user(), $request->validated());

            return $this->successRedirect('profile.edit', 'Profile and organization information successfully updated.');
        } catch (\Exception $e) {
            return $this->errorRedirect($e->getMessage());
        }
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        try {
            $this->profileService->updatePassword($request->user(), $request->password);

            return $this->successRedirect('profile.edit', 'Password successfully updated.');
        } catch (\Exception $e) {
            return $this->errorRedirect($e->getMessage());
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        if (!Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => __('The provided password is incorrect.'),
            ])->errorBag('userDeletion');
        }

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
