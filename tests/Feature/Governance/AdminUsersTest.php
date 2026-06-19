<?php

namespace Tests\Feature\Governance;

use App\Models\User;
use App\Models\AssessmentSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    protected function regularUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['role' => 'user', 'status' => 'active'], $attrs));
    }

    // ─── Index ───────────────────────────────────────────────────────────────

    public function test_admin_can_view_users_index_with_kpi(): void
    {
        $admin = $this->adminUser();
        $this->regularUser();
        $this->regularUser(['status' => 'suspended']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Total Users')
            ->assertSee('Users');
    }

    public function test_non_admin_cannot_access_users_index(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertRedirect();
    }

    public function test_admin_can_search_users(): void
    {
        $admin  = $this->adminUser();
        $target = $this->regularUser(['name' => 'Unique Sentinel User XYZ']);
        $other  = $this->regularUser(['name' => 'Other Person']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'Unique Sentinel User XYZ']))
            ->assertOk()
            ->assertSee('Unique Sentinel User XYZ')
            ->assertDontSee('Other Person');
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $admin   = $this->adminUser();
        $regular = $this->regularUser();

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'admin']))
            ->assertOk()
            ->assertSee($admin->name)
            ->assertDontSee($regular->name);
    }

    public function test_admin_can_filter_users_by_status(): void
    {
        $admin     = $this->adminUser();
        $active    = $this->regularUser(['status' => 'active']);
        $suspended = $this->regularUser(['status' => 'suspended']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['status' => 'suspended']))
            ->assertOk()
            ->assertSee($suspended->name)
            ->assertDontSee($active->name);
    }

    // ─── Create / Store ───────────────────────────────────────────────────────

    public function test_admin_can_view_create_user_form(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Create User');
    }

    public function test_admin_can_create_new_user(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name'              => 'New Test User',
                'email'             => 'newtest@example.com',
                'password'          => 'password123',
                'password_confirmation' => 'password123',
                'role'              => 'user',
                'status'            => 'active',
                'organization_name' => 'Test Corp',
                'business_sector'   => 'Technology',
                'organization_scale'=> 'SME',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newtest@example.com',
            'name'  => 'New Test User',
        ]);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_user_detail(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser(['organization_name' => 'Acme Corp']);

        AssessmentSession::create([
            'user_id' => $user->id,
            'name'    => 'Session Alpha',
            'status'  => 'completed',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('Acme Corp')
            ->assertSee('Activity Stats')
            ->assertSee('Session Alpha');
    }

    // ─── Edit / Update ────────────────────────────────────────────────────────

    public function test_admin_can_edit_and_update_user(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $user))
            ->assertOk();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name'              => 'Updated Name',
                'email'             => $user->email,
                'role'              => 'user',
                'status'            => 'active',
                'organization_name' => 'New Org',
            ])
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name'   => $admin->name,
                'email'  => $admin->email,
                'role'   => 'user', // trying to demote self
                'status' => 'active',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    // ─── Toggle Status ────────────────────────────────────────────────────────

    public function test_admin_can_suspend_and_activate_user(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser(['status' => 'active']);

        // Suspend
        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-status', $user))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'suspended']);

        // Re-activate
        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-status', $user))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'active']);
    }

    public function test_admin_cannot_suspend_themselves(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-status', $admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'status' => 'active']);
    }

    // ─── Reset Password ───────────────────────────────────────────────────────

    public function test_admin_can_reset_user_password(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        $this->actingAs($admin)
            ->post(route('admin.users.reset-password', $user), [
                'password'              => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123', $user->password));
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function test_admin_can_delete_user(): void
    {
        $admin = $this->adminUser();
        $user  = $this->regularUser();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
