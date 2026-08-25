<?php

namespace Tests\Feature;

use App\Models\AdminFile;
use App\Support\AdminAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_without_gate_redirects_home(): void
    {
        $this->get('/admin')->assertRedirect('/');
        $this->get('/admin/files')->assertRedirect('/');
    }

    public function test_admin_with_wrong_gate_redirects_home(): void
    {
        $this->get('/admin?420=70')->assertRedirect('/');
    }

    public function test_valid_gate_shows_password_form(): void
    {
        $this->get('/admin?420=69')
            ->assertOk()
            ->assertViewIs('admin.unlock');
    }

    public function test_wrong_password_stays_on_form_without_errors(): void
    {
        $this->get('/admin?420=69');

        $this->post('/admin', ['password' => 'wrong'])
            ->assertOk()
            ->assertViewIs('admin.unlock')
            ->assertSessionHasNoErrors();
    }

    public function test_correct_password_unlocks_files_page(): void
    {
        $this->get('/admin?420=69');

        $this->post('/admin', ['password' => 'test-admin-password'])
            ->assertRedirect(route('admin.files.index'));

        $this->get('/admin/files')
            ->assertOk()
            ->assertSee('Fișiere', false);
    }

    public function test_files_page_requires_unlock_after_gate(): void
    {
        $this->get('/admin?420=69');

        $this->get('/admin/files')->assertRedirect(route('admin.unlock'));
    }

    public function test_user_can_upload_a_file_with_extra(): void
    {
        Storage::fake('local');

        $response = $this->asAdmin()->post('/admin/files', [
            'file' => UploadedFile::fake()->create('report.pdf', 20),
            'extra' => 'copie factură',
        ]);

        $response->assertRedirect(route('admin.files.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('admin_files', [
            'name' => 'report.pdf',
            'extra' => 'copie factură',
        ]);

        $file = AdminFile::query()->firstOrFail();
        Storage::disk('local')->assertExists($file->stored_path);
    }

    public function test_duplicate_upload_names_get_windows_style_suffix(): void
    {
        Storage::fake('local');

        $this->asAdmin()->post('/admin/files', [
            'file' => UploadedFile::fake()->create('report.pdf', 10),
        ]);
        $this->asAdmin()->post('/admin/files', [
            'file' => UploadedFile::fake()->create('report.pdf', 10),
        ]);

        $this->assertDatabaseHas('admin_files', ['name' => 'report.pdf']);
        $this->assertDatabaseHas('admin_files', ['name' => 'report (1).pdf']);
    }

    public function test_download_uses_the_display_name(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('admin-files/stored.pdf', 'contents');

        $file = AdminFile::factory()->create([
            'name' => 'report (1).pdf',
            'stored_path' => 'admin-files/stored.pdf',
        ]);

        $this->asAdmin()
            ->get(route('admin.files.download', $file))
            ->assertDownload('report (1).pdf');
    }

    public function test_file_name_and_extra_can_be_updated(): void
    {
        $file = AdminFile::factory()->create([
            'name' => 'old.pdf',
            'extra' => 'before',
        ]);

        $this->asAdmin()
            ->put(route('admin.files.update', $file), [
                'name' => 'new.pdf',
                'extra' => 'after',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('admin_files', [
            'id' => $file->id,
            'name' => 'new.pdf',
            'extra' => 'after',
        ]);
    }

    public function test_rename_rejects_a_name_that_already_exists(): void
    {
        $file = AdminFile::factory()->create(['name' => 'one.pdf']);
        AdminFile::factory()->create(['name' => 'two.pdf']);

        $this->asAdmin()
            ->from(route('admin.files.index'))
            ->put(route('admin.files.update', $file), [
                'name' => 'two.pdf',
            ])
            ->assertRedirect(route('admin.files.index'))
            ->assertSessionHasErrors('name');

        $this->assertDatabaseHas('admin_files', [
            'id' => $file->id,
            'name' => 'one.pdf',
        ]);
    }

    public function test_wrong_delete_password_does_not_delete_the_file(): void
    {
        $file = AdminFile::factory()->create();

        $this->asAdmin()
            ->deleteJson(route('admin.files.destroy', $file), [
                'password' => 'wrong',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('admin_files', ['id' => $file->id]);
    }

    public function test_correct_delete_password_deletes_file_and_storage(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('admin-files/stored.pdf', 'contents');

        $file = AdminFile::factory()->create([
            'stored_path' => 'admin-files/stored.pdf',
        ]);

        $this->asAdmin()
            ->deleteJson(route('admin.files.destroy', $file), [
                'password' => 'test-admin-password',
            ])
            ->assertOk();

        $this->assertDatabaseMissing('admin_files', ['id' => $file->id]);
        Storage::disk('local')->assertMissing('admin-files/stored.pdf');
    }

    public function test_files_are_paginated_thirty_per_page(): void
    {
        AdminFile::factory()->count(31)->create();

        $pageOne = $this->asAdmin()->get('/admin/files');
        $pageOne->assertOk();
        $this->assertCount(30, $pageOne->viewData('files'));

        $pageTwo = $this->asAdmin()->get('/admin/files?page=2');
        $pageTwo->assertOk();
        $this->assertCount(1, $pageTwo->viewData('files'));
    }

    public function test_files_can_be_sorted_alphabetically_or_chronologically(): void
    {
        AdminFile::factory()->create([
            'name' => 'zeta.pdf',
            'created_at' => now(),
        ]);
        AdminFile::factory()->create([
            'name' => 'alpha.pdf',
            'created_at' => now()->subDay(),
        ]);

        $chronological = $this->asAdmin()->get('/admin/files')->viewData('files')->pluck('name')->all();
        $this->assertSame(['zeta.pdf', 'alpha.pdf'], $chronological);

        $alphabetical = $this->asAdmin()->get('/admin/files?sort=name')->viewData('files')->pluck('name')->all();
        $this->assertSame(['alpha.pdf', 'zeta.pdf'], $alphabetical);
    }

    public function test_files_can_be_searched_by_name_extra_and_date(): void
    {
        $this->travelTo('2026-08-25 16:45:00');

        AdminFile::factory()->create([
            'name' => 'invoice.pdf',
            'extra' => 'secret note',
        ]);
        AdminFile::factory()->create([
            'name' => 'other.pdf',
            'extra' => 'nothing',
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ]);

        $this->asAdmin()->get('/admin/files?q=invoice')->assertSee('invoice.pdf', false)->assertDontSee('other.pdf', false);
        $this->asAdmin()->get('/admin/files?q=secret')->assertSee('invoice.pdf', false)->assertDontSee('other.pdf', false);
        $this->asAdmin()->get('/admin/files?q=25.08.2026')->assertSee('invoice.pdf', false)->assertDontSee('other.pdf', false);
    }

    public function test_upload_without_a_file_shows_a_validation_error(): void
    {
        $this->asAdmin()
            ->from(route('admin.files.index'))
            ->post('/admin/files', ['extra' => 'x'])
            ->assertRedirect(route('admin.files.index'))
            ->assertSessionHasErrors('file');
    }

    private function asAdmin(): TestCase
    {
        return $this->withSession([
            AdminAuth::SESSION_GATE => true,
            AdminAuth::SESSION_UNLOCKED => true,
        ]);
    }
}
