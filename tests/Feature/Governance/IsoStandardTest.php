<?php

namespace Tests\Feature\Governance;

use App\Models\IsoStandard;
use App\Models\User;
use App\Models\AssessmentSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class IsoStandardTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    protected function createRegularUser(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_view_iso_standards_index(): void
    {
        $admin = $this->createAdminUser();
        
        IsoStandard::create([
            'type' => 'clause',
            'level' => '1',
            'code' => '4.1',
            'title' => 'Understanding the Organization',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.standards.index'))
            ->assertOk()
            ->assertSee('ISO 27001 Standards')
            ->assertSee('4.1')
            ->assertSee('Understanding the Organization');
    }

    public function test_non_admin_cannot_access_iso_standards(): void
    {
        $user = $this->createRegularUser();

        $this->actingAs($user)
            ->get(route('admin.standards.index'))
            ->assertRedirect();
    }

    public function test_admin_can_create_iso_standard(): void
    {
        $admin = $this->createAdminUser();

        $payload = [
            'type' => 'control',
            'level' => '2',
            'code' => 'A.5.1',
            'title' => 'Policies for information security',
            'description' => 'Information security policies should be defined.',
            'questions' => ['Is there a signed policy?', 'Is it reviewed?'],
            'implementation_guidance' => 'Ensure CISO signs it.',
        ];

        $this->actingAs($admin)
            ->post(route('admin.standards.store'), $payload)
            ->assertRedirect(route('admin.standards.index'));

        $this->assertDatabaseHas('iso_standards', [
            'code' => 'A.5.1',
            'title' => 'Policies for information security',
        ]);
    }

    public function test_admin_can_update_iso_standard(): void
    {
        $admin = $this->createAdminUser();
        
        $standard = IsoStandard::create([
            'type' => 'clause',
            'level' => '1',
            'code' => '5.1',
            'title' => 'Leadership',
        ]);

        $payload = [
            'type' => 'clause',
            'level' => '1',
            'code' => '5.1',
            'title' => 'Leadership Updated',
            'questions' => ['Question 1'],
        ];

        $this->actingAs($admin)
            ->put(route('admin.standards.update', $standard), $payload)
            ->assertRedirect(route('admin.standards.index'));

        $this->assertDatabaseHas('iso_standards', [
            'id' => $standard->id,
            'title' => 'Leadership Updated',
        ]);
    }

    public function test_admin_cannot_delete_standard_with_results(): void
    {
        $admin = $this->createAdminUser();
        
        $standard = IsoStandard::create([
            'type' => 'clause',
            'level' => '1',
            'code' => '6.1',
            'title' => 'Planning',
        ]);

        $session = AssessmentSession::create([
            'user_id' => $admin->id,
            'name' => 'Test Session',
            'status' => 'in_progress',
        ]);

        $standard->results()->create([
            'session_id' => $session->id,
            'status' => 'in_progress',
            'is_applicable' => true,
            'maturity_rating' => 1,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.standards.index'))
            ->delete(route('admin.standards.destroy', $standard))
            ->assertRedirect(route('admin.standards.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('iso_standards', ['id' => $standard->id]);
    }

    public function test_admin_can_export_iso_standards_to_csv(): void
    {
        $admin = $this->createAdminUser();

        IsoStandard::create([
            'type' => 'clause',
            'level' => '1',
            'code' => '4.2',
            'title' => 'Interested Parties',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.standards.export'));

        $response->assertOk();

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('parent_code,type,level,code,title', $content);
        $this->assertStringContainsString('4.2', $content);
        $this->assertStringContainsString('Interested Parties', $content);
    }

    public function test_admin_can_import_iso_standards_from_csv(): void
    {
        $admin = $this->createAdminUser();

        $csvContent = "parent_code,type,level,code,title,description,questions,implementation_guidance\n"
                    . ",clause,1,4.3,Determining Scope,Scope of ISMS,[\"Question A\"],Guidance A\n"
                    . "4.3,clause,2,4.3.1,Sub Scope,Details,[\"Question B\"],Guidance B\n";

        $file = UploadedFile::fake()->createWithContent('standards.csv', $csvContent);

        $this->actingAs($admin)
            ->post(route('admin.standards.import'), [
                'csv_file' => $file,
            ])
            ->assertRedirect(route('admin.standards.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('iso_standards', [
            'code' => '4.3',
            'title' => 'Determining Scope',
        ]);

        $child = IsoStandard::where('code', '4.3.1')->firstOrFail();
        $parent = IsoStandard::where('code', '4.3')->firstOrFail();

        $this->assertEquals($parent->id, $child->parent_id);
    }
}
