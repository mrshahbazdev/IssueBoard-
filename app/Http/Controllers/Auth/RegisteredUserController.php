<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (User::query()->exists()) {
            return redirect()
                ->route('login')
                ->with('status', __('app.register.closed'));
        }

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        if (User::query()->exists()) {
            return redirect()
                ->route('login')
                ->with('status', __('app.register.closed'));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = DB::transaction(function () use ($data): User {
            if (User::query()->lockForUpdate()->first()) {
                throw ValidationException::withMessages([
                    'email' => __('app.register.closed'),
                ]);
            }

            return User::create($data + ['role' => UserRole::Admin]);
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('issueboard.index');
    }
}
