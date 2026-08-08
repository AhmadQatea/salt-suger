<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the administrator account settings form.
     */
    public function edit(): View
    {
        return view('admin.profile', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the administrator's email and optionally password.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->email = $validated['email'];

        $passwordChanged = filled($validated['password'] ?? null);

        if ($passwordChanged) {
            $user->password = $validated['password'];
        }

        $user->save();

        if ($passwordChanged) {
            Auth::logoutOtherDevices($validated['password']);

            $request->session()->put([
                'password_hash_'.Auth::getDefaultDriver() => $user->getAuthPassword(),
            ]);

            $request->session()->regenerate();
        }

        return redirect()
            ->route('admin.profile')
            ->with('status', __('تم حفظ التغييرات بنجاح.'));
    }
}
