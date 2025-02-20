<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ResetPassword;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthServiceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['router']->aliasMiddleware('auth.jwt', \Tymon\JWTAuth\Http\Middleware\Authenticate::class);
    }

    public function test_user_registration()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_user_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user1@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'token', 'user']);
    }

    public function test_user_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user2@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(500);
        // ->assertJson(['message' => 'Invalid credentials']);
    }
    public function test_send_reset_link_successfully()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => fake()->unique()->safeEmail,
        ]);

        $response = $this->postJson('/api/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
        // ->assertJson(['success' => true]);

        $this->assertDatabaseHas('reset_passwords', ['email' => $user->email]);
        Mail::assertSent(ForgotPasswordMail::class);
    }

    public function test_reset_password_with_valid_otp()
    {
        $user = User::factory()->create([
            'email' => fake()->unique()->safeEmail,
        ]);

        $resetRecord = ResetPassword::create([
            'email' => $user->email,
            'otp' => '123456',
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200);
        // ->assertJson(['success' => true]);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        $this->assertDatabaseMissing('reset_passwords', ['email' => $user->email]);
    }

    public function test_reset_password_with_invalid_otp()
    {
        $user = User::factory()->create([
            'email' => fake()->unique()->safeEmail,
        ]);

        ResetPassword::create([
            'email' => 'john@example.com',
            'otp' => 'dwdwff',
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'john@example.com',
            'otp' => 'weeeeee',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'

        ]);

        $response->assertStatus(400);
        // ->assertJson(['success' => false, 'message' => 'Invalid OTP']);
    }


    public function test_logout_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->postJson('/api/logout', [], ['Authorization' => "Bearer $token"]);

        $response->assertStatus(200);

        $this->assertTrue(JWTAuth::parseToken()->check() === false);
    }
}
