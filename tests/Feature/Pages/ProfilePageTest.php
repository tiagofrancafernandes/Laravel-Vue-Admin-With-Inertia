<?php

namespace Tests\Feature\Pages;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Unauthenticated user cannot access profile page.
     */
    public function testUnauthenticatedUserCannotAccessProfilePage(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    /**
     * Authenticated user can access profile edit page.
     */
    public function testAuthenticatedUserCanAccessProfileEditPage(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
            ->component('Profile/Edit')
            ->has('auth.user')
        );
    }

    /**
     * User can update their profile information.
     */
    public function testUserCanUpdateTheirProfileInformation(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'New Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    }

    /**
     * User can change email address.
     */
    public function testUserCanChangeEmailAddress(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => 'new@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com',
        ]);
    }

    /**
     * User cannot use email that already exists.
     */
    public function testUserCannotUseEmailThatAlreadyExists(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $otherUser->email,
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * User can keep their current email.
     */
    public function testUserCanKeepTheirCurrentEmail(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Updated Name',
            'email' => 'user@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'user@example.com',
        ]);
    }

    /**
     * Unauthenticated user cannot delete account.
     */
    public function testUnauthenticatedUserCannotDeleteAccount(): void
    {
        $user = User::factory()->create();

        $response = $this->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * User can delete their account with correct password.
     */
    public function testUserCanDeleteTheirAccountWithCorrectPassword(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * User cannot delete account with incorrect password.
     */
    public function testUserCannotDeleteAccountWithIncorrectPassword(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * Profile update validates required fields.
     */
    public function testProfileUpdateValidatesRequiredFields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', []);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    /**
     * Profile update validates email format.
     */
    public function testProfileUpdateValidatesEmailFormat(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Profile update validates name max length.
     */
    public function testProfileUpdateValidatesNameMaxLength(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => str_repeat('a', 256),
            'email' => $user->email,
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Profile update validates email max length.
     */
    public function testProfileUpdateValidatesEmailMaxLength(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Test User',
            'email' => str_repeat('a', 250) . '@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
