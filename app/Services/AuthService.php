<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\ResetPassword;
use App\Mail\ForgotPasswordMail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = JWTAuth::fromUser($user);

        return ['token' => $token];
    }

    public function login(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    public function sendResetLink($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ['success' => false, 'message' => 'Email not found.'];
        }

        $otp = Str::random(6);

        ResetPassword::create([
            'email' => $email,
            'otp' => $otp,
            'created_at' => now(),
        ]);

        Mail::to($email)->send(new ForgotPasswordMail($otp));

        return ['success' => true, 'message' => 'Password reset link has been sent to your email.'];
    }

    public function resetPassword($data)
    {
        $resetRecord = ResetPassword::where('email', $data['email'])
            ->where('otp', $data['otp'])
            ->first();

        if (!$resetRecord) {
            return ['success' => false, 'message' => 'Invalid OTP.'];
        }

        $otpCreated = Carbon::parse($resetRecord->created_at);
        if ($otpCreated->diffInMinutes(Carbon::now()) > 30) {
            return ['success' => false, 'message' => 'OTP has expired.'];
        }

        $user = User::where('email', $data['email'])->first();
        $user->update(['password' => bcrypt($data['password'])]);

        $resetRecord->delete();

        return ['success' => true, 'message' => 'Password has been reset successfully.'];
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
