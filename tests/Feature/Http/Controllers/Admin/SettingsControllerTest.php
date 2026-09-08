<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_view_only_public_site_settings(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Informasi situs')
            ->assertSee('Nomor WhatsApp')
            ->assertDontSee('APP_KEY')
            ->assertDontSee('SMTP_PASSWORD')
            ->assertDontSee('ADMIN_PASSWORD_HASH');
    }

    public function test_admin_can_override_settings_used_by_public_pages(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $values = $this->settings([
            'tagline' => 'Pasokan terpercaya untuk semua dapur.',
            'business_summary' => 'Ringkasan baru dari admin.',
            'nib_issued_at' => '8 September 2026',
            'company_name' => 'Brand Baru',
            'email' => 'mitra@example.com',
            'phone' => '021-555-0101',
            'whatsapp' => '628111111111',
            'hero_title' => 'Headline baru dari admin.',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), ['settings' => $values])
            ->assertRedirect();

        $this->assertDatabaseHas('site_settings', ['key' => 'email', 'value' => 'mitra@example.com']);
        $this->assertDatabaseHas('site_settings', ['key' => 'whatsapp', 'value' => '628111111111']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Headline baru dari admin.')
            ->assertSee('Pasokan terpercaya untuk semua dapur.');

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('mitra@example.com')
            ->assertSee('021-555-0101')
            ->assertSee('WhatsApp Brand Baru')
            ->assertSee('https://wa.me/628111111111', false);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Ringkasan baru dari admin.')
            ->assertSee('8 September 2026');
    }

    public function test_customer_cannot_manage_site_settings(): void
    {
        $customer = User::factory()->create(['role' => 'CUSTOMER']);

        $this->actingAs($customer)->get(route('admin.settings.index'))->assertForbidden();
        $this->actingAs($customer)->put(route('admin.settings.update'), ['settings' => $this->settings()])->assertForbidden();
        $this->actingAs($customer)->delete(route('admin.settings.reset'))->assertForbidden();
    }

    public function test_admin_can_reset_overrides_to_env_defaults(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        SiteSetting::query()->create(['key' => 'tagline', 'value' => 'Override sementara']);

        $this->actingAs($admin)
            ->delete(route('admin.settings.reset'))
            ->assertRedirect();

        $this->assertDatabaseMissing('site_settings', ['key' => 'tagline']);
        $this->get(route('about'))->assertSee(config('novastra.company.tagline'));
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function settings(array $overrides = []): array
    {
        return array_replace(config('novastra.company'), $overrides);
    }
}
