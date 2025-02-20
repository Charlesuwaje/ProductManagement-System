<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $response = $this->authService->register($request->validated());

        return response()->json(['message' => 'User registered successfully.', 'token' => $response['token']], 201);
    }

    // public function login(LoginRequest $request)
    // {
    //     $response = $this->authService->login($request->validated());

    //     if (!$response->status() === 401) {
    //         return $response;
    //     }

    //     return $response;
    // }
    public function login(LoginRequest $request)
    {
        $response = $this->authService->login($request->validated());

        if (!$response->status() === 401) {
            return $response;
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($response->original['user']),
            'token' => $response->original['token'],
        ]);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $response = $this->authService->sendResetLink($request->email);

        if (!$response['success']) {
            return response()->json(['error' => $response['message']], 400);
        }

        return response()->json(['message' => $response['message']], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
            'password' => 'required|min:6|confirmed'
        ]);

        $response = $this->authService->resetPassword($request->all());

        if (!$response['success']) {
            return response()->json(['error' => $response['message']], 400);
        }

        return response()->json(['message' => $response['message']], 200);
    }
    public function logout()
    {
        $this->authService->logout();

        return response()->json(['message' => 'Logged out successfully.']);
    }

}
