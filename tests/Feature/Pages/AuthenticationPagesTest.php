<?php

namespace Tests\Feature\Pages;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuthenticationPagesTest extends TestCase
{
    use RefreshDatabase;

    // ==================== LOGIN TESTS ====================

    /**
     * Login screen can be rendered.
     */
    public function testLoginScreenCanBeRendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/Login')
        );
    }

    /**
     * User can authenticate using the login screen.
     */
    public function testUserCanAuthenticateUsingTheLoginScreen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    /**
     * User cannot authenticate with invalid password.
     */
    public function testUserCannotAuthenticateWithInvalidPassword(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /**
     * User cannot authenticate with non-existent email.
     */
    public function testUserCannotAuthenticateWithNonExistentEmail(): void
    {
        $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    /**
     * Login validates required fields.
     */
    public function testLoginValidatesRequiredFields(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    /**
     * Login validates email format.
     */
    public function testLoginValidatesEmailFormat(): void
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * User with unverified email can still login.
     */
    public function testUserWithUnverifiedEmailCanStillLogin(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }

    /**
     * Remember me functionality works.
     */
    public function testRememberMeFunctionalityWorks(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        // Token should be set in cookies
        $this->assertNotNull($response->getCookie('remember_web_59ba36addc2b2f9401580f014c7f58ea4737b34c'));
    }

    /**
     * Authenticated user cannot access login page.
     */
    public function testAuthenticatedUserCannotAccessLoginPage(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }

    // ==================== REGISTER TESTS ====================

    /**
     * Registration screen can be rendered.
     */
    public function testRegistrationScreenCanBeRendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/Register')
        );
    }

    /**
     * New users can register.
     */
    public function testNewUsersCanRegister(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        $this->assertAuthenticated();
    }

    /**
     * Registration validates name required.
     */
    public function testRegistrationValidatesNameRequired(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Registration validates email required.
     */
    public function testRegistrationValidatesEmailRequired(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => '',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Registration validates email format.
     */
    public function testRegistrationValidatesEmailFormat(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Registration validates email uniqueness.
     */
    public function testRegistrationValidatesEmailUniqueness(): void
    {
        $existingUser = User::factory()->create();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => $existingUser->email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Registration validates password required.
     */
    public function testRegistrationValidatesPasswordRequired(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Registration validates password confirmation.
     */
    public function testRegistrationValidatesPasswordConfirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Different123!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Registration validates password strength.
     */
    public function testRegistrationValidatesPasswordStrength(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Registration validates name max length.
     */
    public function testRegistrationValidatesNameMaxLength(): void
    {
        $response = $this->post('/register', [
            'name' => str_repeat('a', 256),
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Registered user has correct default attributes.
     */
    public function testRegisteredUserHasCorrectDefaultAttributes(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('newuser@example.com', $user->email);
        $this->assertEquals('user', $user->role);
    }

    /**
     * Authenticated user cannot access registration page.
     */
    public function testAuthenticatedUserCannotAccessRegistrationPage(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/dashboard');
    }

    // ==================== LOGOUT TESTS ====================

    /**
     * User can logout successfully.
     */
    public function testUserCanLogoutSuccessfully(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }

    /**
     * Unauthenticated user cannot logout.
     */
    public function testUnauthenticatedUserCannotLogout(): void
    {
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
    }

    // ==================== EMAIL VERIFICATION TESTS ====================

    /**
     * Email verification screen can be rendered.
     */
    public function testEmailVerificationScreenCanBeRendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/VerifyEmail')
        );
    }

    /**
     * Verified user cannot see email verification screen.
     */
    public function testVerifiedUserCannotSeeEmailVerificationScreen(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertRedirect('/dashboard');
    }

    /**
     * Unauthenticated user cannot access email verification screen.
     */
    public function testUnauthenticatedUserCannotAccessEmailVerificationScreen(): void
    {
        $response = $this->get('/verify-email');

        $response->assertRedirect('/login');
    }

    /**
     * Email can be verified with valid hash.
     */
    public function testEmailCanBeVerifiedWithValidHash(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    /**
     * Email is not verified with invalid hash.
     */
    public function testEmailIsNotVerifiedWithInvalidHash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    // ==================== PASSWORD RESET TESTS ====================

    /**
     * Forgot password screen can be rendered.
     */
    public function testForgotPasswordScreenCanBeRendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/ForgotPassword')
        );
    }

    /**
     * Password reset link screen can be requested.
     */
    public function testPasswordResetLinkCanBeRequested(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        // Should succeed even if user doesn't exist (security)
        $response->assertSessionHasNoErrors();
    }

    /**
     * Forgot password validates email required.
     */
    public function testForgotPasswordValidatesEmailRequired(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => '',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Reset password screen can be rendered.
     */
    public function testResetPasswordScreenCanBeRendered(): void
    {
        $user = User::factory()->create();

        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $response = $this->get("/reset-password/{$token}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/ResetPassword')
            ->has('token')
        );
    }

    /**
     * Password can be reset with valid token.
     */
    public function testPasswordCanBeResetWithValidToken(): void
    {
        $user = User::factory()->create();
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewPassword123!', $user->fresh()->password));
    }

    /**
     * Reset password validates password required.
     */
    public function testResetPasswordValidatesPasswordRequired(): void
    {
        $user = User::factory()->create();
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Reset password validates password confirmation.
     */
    public function testResetPasswordValidatesPasswordConfirmation(): void
    {
        $user = User::factory()->create();
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'Different123!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Reset password validates password strength.
     */
    public function testResetPasswordValidatesPasswordStrength(): void
    {
        $user = User::factory()->create();
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
