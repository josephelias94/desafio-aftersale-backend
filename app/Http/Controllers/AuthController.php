<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request): JsonResponse
    {
        $user = (new User)->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->tokenJson(
            $user->createToken('auth_token')->plainTextToken
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (Hash::check($request->password, $user->password)) {
            $user->tokens()->delete();

            return response()->tokenJson(
                $user->createToken('auth_token')->plainTextToken
            );
        }

        return response()->errorJson('Invalid login details', 422);
    }
}
