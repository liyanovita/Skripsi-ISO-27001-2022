<?php

namespace Tests\Feature\Compliance;

use App\Models\User;
use App\Models\Organization;
use App\Models\AssessmentSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_organizations_list()
    {
        $response = $this->get(route('admin.organizations.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_organizations_list()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.organizations.index'));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_access_organizations_list()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $org = Organization::create([
            'name' => 'Test KPK Corp',
            'code' => 'KPKC',
            'contact_email' => 'kpk@test.com'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.organizations.index'));
        $response->assertOk();
        $response->assertSee('Test KPK Corp');
        $response->assertSee('KPKC');
    }


    public function test_admin_can_create_organization()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($admin)->post(route('admin.organizations.store'), [
            'name' => 'New Organization Tech',
            'code' => 'NOTECH',
            'contact_email' => 'notech@test.com',
            'contact_phone' => '0812345678',
            'description' => 'Test Desc',
            'address' => 'Test Address'
        ]);

        $response->assertRedirect(route('admin.organizations.index'));
        $this->assertDatabaseHas('organizations', [
            'name' => 'New Organization Tech',
            'code' => 'NOTECH',
            'contact_email' => 'notech@test.com'
        ]);
    }

    public function test_admin_can_edit_organization()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $org = Organization::create([
            'name' => 'Old Organization',
            'code' => 'OLD',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.organizations.update', $org->id), [
            'name' => 'Updated Organization',
            'code' => 'UPD',
            'contact_email' => 'updated@test.com'
        ]);

        $response->assertRedirect(route('admin.organizations.index'));
        $this->assertDatabaseHas('organizations', [
            'id' => $org->id,
            'name' => 'Updated Organization',
            'code' => 'UPD',
            'contact_email' => 'updated@test.com'
        ]);
    }

    public function test_admin_can_delete_organization()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $org = Organization::create([
            'name' => 'Delete Me Corp',
            'code' => 'DEL',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.organizations.destroy', $org->id));

        $response->assertRedirect(route('admin.organizations.index'));
        $this->assertSoftDeleted('organizations', [
            'id' => $org->id
        ]);
    }
}
