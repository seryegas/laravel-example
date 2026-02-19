<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Core\Enums\UserRole;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function register(RegisterRequest $request): array
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => bcrypt($request->validated('password')),
            'role' => UserRole::Client,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function login(LoginRequest $request): ?string
    {
        if (!Auth::attempt($request->validated())) {
            return null;
        }

        /** @var User $user */
        $user = Auth::user();

        return $user->createToken('auth-token')->plainTextToken;
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
