<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Support\Seo;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function show(Request $request)
    {
                Seo::set([
            'title' => Seo::tr('My account', 'حسابي'),
            'noindex' => true,
        ]);
        return Inertia::render('Account', [
            'profile' => $request->user()->only(['name', 'email', 'phone']),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($data);

        return back()->with('status', __('store.profile_updated'));
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', __('store.password_changed'));
    }
}
