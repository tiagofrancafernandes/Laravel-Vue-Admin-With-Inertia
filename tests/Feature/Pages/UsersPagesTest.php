<?php

namespace Tests\Feature\Pages;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsersPagesTest extends TestCase
{
    use RefreshDatabase;

    // ==================== INDEX TESTS ====================

    /**
     * Unauthenticated user cannot access users index.
     */
    public function testUnauthenticatedUserCannotAccessUsersIndex(): void
    {
        $response = $this->get('/users');

        $response->assertRedirect('/login');
    }

    /**
     * Non-admin user cannot access users index.
     */
    public function testNonAdminUserCannotAccessUsersIndex(): void
    {
        $user = User::factory()->withRole('user')->create();

        $response = $this->actingAs($user)->get('/users');

        $response->assertForbidden();
    }

    /**
     * Admin user can access users index.
     */
    public function testAdminUserCanAccessUsersIndex(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Resources/Users/Index')
            ->has('users')
            ->has('filters')
        );
    }

    /**
     * Users index displays paginated users.
     */
    public function testUsersIndexDisplaysPaginatedUsers(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(20)->create();

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('users.data', 15) // Default pagination is 15
            ->has('users.total', 21) // 20 created + 1 admin
        );
    }

    /**
     * Users index search filter works.
     */
    public function testUsersIndexSearchFilter(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($admin)->get('/users?search=john');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.name', 'John Doe')
        );
    }

    /**
     * Users index role filter works.
     */
    public function testUsersIndexRoleFilter(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(3)->withRole('user')->create();

        $response = $this->actingAs($admin)->get('/users?role=admin');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.role', 'admin')
        );
    }

    /**
     * Users index preserves filters in pagination.
     */
    public function testUsersIndexPreservesFiltersInPagination(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(20)->withRole('user')->create();

        $response = $this->actingAs($admin)->get('/users?role=user&page=2');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('users.data')
            ->has('users.next_page_url')
        );
    }

    // ==================== CREATE PAGE TESTS ====================

    /**
     * Unauthenticated user cannot access create user page.
     */
    public function testUnauthenticatedUserCannotAccessCreateUserPage(): void
    {
        $response = $this->get('/users/create');

        $response->assertRedirect('/login');
    }

    /**
     * Non-admin user cannot access create user page.
     */
    public function testNonAdminUserCannotAccessCreateUserPage(): void
    {
        $user = User::factory()->withRole('user')->create();

        $response = $this->actingAs($user)->get('/users/create');

        $response->assertForbidden();
    }

    /**
     * Admin user can access create user page.
     */
    public function testAdminUserCanAccessCreateUserPage(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/users/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Resources/Users/Create')
        );
    }

    // ==================== STORE (CREATE) TESTS ====================

    /**
     * Unauthenticated user cannot create user.
     */
    public function testUnauthenticatedUserCannotCreateUser(): void
    {
        $response = $this->post('/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'user',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Non-admin user cannot create user.
     */
    public function testNonAdminUserCannotCreateUser(): void
    {
        $user = User::factory()->withRole('user')->create();

        $response = $this->actingAs($user)->post('/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'user',
        ]);

        $response->assertForbidden();
    }

    /**
     * Admin can create user with valid data.
     */
    public function testAdminCanCreateUserWithValidData(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'user',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'user',
        ]);
    }

    /**
     * Create user validates required fields.
     */
    public function testCreateUserValidatesRequiredFields(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    /**
     * Create user validates email uniqueness.
     */
    public function testCreateUserValidatesEmailUniqueness(): void
    {
        $admin = User::factory()->admin()->create();
        $existingUser = User::factory()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'New User',
            'email' => $existingUser->email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Create user validates password strength.
     */
    public function testCreateUserValidatesPasswordStrength(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Create user validates role field.
     */
    public function testCreateUserValidatesRole(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'invalid-role',
        ]);

        $response->assertSessionHasErrors('role');
    }

    /**
     * Created user is properly hashed.
     */
    public function testCreatedUserPasswordIsHashed(): void
    {
        $admin = User::factory()->admin()->create();
        $password = 'Password123!';

        $this->actingAs($admin)->post('/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => $password,
            'password_confirmation' => $password,
            'role' => 'user',
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue(Hash::check($password, $user->password));
    }

    // ==================== SHOW TESTS ====================

    /**
     * Unauthenticated user cannot view user.
     */
    public function testUnauthenticatedUserCannotViewUser(): void
    {
        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}");

        $response->assertRedirect('/login');
    }

    /**
     * Non-admin user cannot view user.
     */
    public function testNonAdminUserCannotViewUser(): void
    {
        $regularUser = User::factory()->withRole('user')->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($regularUser)->get("/users/{$otherUser->id}");

        $response->assertForbidden();
    }

    /**
     * Admin can view user details.
     */
    public function testAdminCanViewUserDetails(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'John Doe']);

        $response = $this->actingAs($admin)->get("/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Resources/Users/Show')
            ->where('user.id', $user->id)
            ->where('user.name', 'John Doe')
        );
    }

    /**
     * View non-existent user returns 404.
     */
    public function testViewNonExistentUserReturns404(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/users/99999');

        $response->assertNotFound();
    }

    // ==================== EDIT TESTS ====================

    /**
     * Unauthenticated user cannot access edit user page.
     */
    public function testUnauthenticatedUserCannotAccessEditUserPage(): void
    {
        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Non-admin user cannot access edit user page.
     */
    public function testNonAdminUserCannotAccessEditUserPage(): void
    {
        $regularUser = User::factory()->withRole('user')->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($regularUser)->get("/users/{$otherUser->id}/edit");

        $response->assertForbidden();
    }

    /**
     * Admin can access edit user page.
     */
    public function testAdminCanAccessEditUserPage(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get("/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Resources/Users/Edit')
            ->where('user.id', $user->id)
        );
    }

    // ==================== UPDATE TESTS ====================

    /**
     * Unauthenticated user cannot update user.
     */
    public function testUnauthenticatedUserCannotUpdateUser(): void
    {
        $user = User::factory()->create();

        $response = $this->patch("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Non-admin user cannot update user.
     */
    public function testNonAdminUserCannotUpdateUser(): void
    {
        $regularUser = User::factory()->withRole('user')->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($regularUser)->patch("/users/{$otherUser->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ]);

        $response->assertForbidden();
    }

    /**
     * Admin can update user with valid data.
     */
    public function testAdminCanUpdateUserWithValidData(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->patch("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'user',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Update user validates required fields.
     */
    public function testUpdateUserValidatesRequiredFields(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->patch("/users/{$user->id}", []);

        $response->assertSessionHasErrors(['name', 'email', 'role']);
    }

    /**
     * Update user validates email uniqueness across other users.
     */
    public function testUpdateUserValidatesEmailUniquenessAcrossOthers(): void
    {
        $admin = User::factory()->admin()->create();
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->actingAs($admin)->patch("/users/{$user2->id}", [
            'name' => 'User Two',
            'email' => $user1->email,
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Update user allows same email for same user.
     */
    public function testUpdateUserAllowsSameEmailForSameUser(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->actingAs($admin)->patch("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'user@example.com',
            'role' => 'user',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'user@example.com',
        ]);
    }

    /**
     * Update user can change password.
     */
    public function testUpdateUserCanChangePassword(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $newPassword = 'NewPassword123!';

        $this->actingAs($admin)->patch("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            'role' => 'user',
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    /**
     * Update user without password keeps old password.
     */
    public function testUpdateUserWithoutPasswordKeepsOldPassword(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $this->actingAs($admin)->patch("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'user',
        ]);

        $user->refresh();
        $this->assertEquals($originalPassword, $user->password);
    }

    // ==================== DELETE TESTS ====================

    /**
     * Unauthenticated user cannot delete user.
     */
    public function testUnauthenticatedUserCannotDeleteUser(): void
    {
        $user = User::factory()->create();

        $response = $this->delete("/users/{$user->id}");

        $response->assertRedirect('/login');
    }

    /**
     * Non-admin user cannot delete user.
     */
    public function testNonAdminUserCannotDeleteUser(): void
    {
        $regularUser = User::factory()->withRole('user')->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($regularUser)->delete("/users/{$otherUser->id}");

        $response->assertForbidden();
    }

    /**
     * Admin can delete user.
     */
    public function testAdminCanDeleteUser(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/users/{$user->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Delete non-existent user returns 404.
     */
    public function testDeleteNonExistentUserReturns404(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete('/users/99999');

        $response->assertNotFound();
    }

    /**
     * Admin cannot delete themselves.
     */
    public function testAdminCannotDeleteThemselves(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete("/users/{$admin->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    // ==================== INTEGRATION TESTS ====================

    /**
     * Full CRUD workflow works correctly.
     */
    public function testFullCrudWorkflowWorks(): void
    {
        $admin = User::factory()->admin()->create();

        // Create
        $this->actingAs($admin)->post('/users', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'user',
        ]);

        $user = User::where('email', 'testuser@example.com')->first();
        $this->assertNotNull($user);

        // Read
        $response = $this->actingAs($admin)->get("/users/{$user->id}");
        $response->assertStatus(200);

        // Update
        $this->actingAs($admin)->patch("/users/{$user->id}", [
            'name' => 'Updated Test User',
            'email' => 'testuser@example.com',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Test User',
            'role' => 'admin',
        ]);

        // Delete
        $this->actingAs($admin)->delete("/users/{$user->id}");
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
