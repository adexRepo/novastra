<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function menuRoutes(): array
    {
        return [
            'Dashboard' => 'admin.dashboard',
            'Produk' => 'admin.products.index',
            'Kategori' => 'admin.categories.index',
            'Pesanan' => 'admin.orders.index',
            'Pembayaran' => 'admin.payments.index',
            'Feedback' => 'admin.feedback.index',
            'Testimoni' => 'admin.testimonials.index',
            'FAQ' => 'admin.faqs.index',
            'Laporan' => 'admin.reports.index',
            'Informasi Situs' => 'admin.settings.index',
        ];
    }

    public function test_every_admin_menu_destination_loads_and_marks_itself_as_current(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        foreach ($this->menuRoutes() as $label => $routeName) {
            $response = $this->actingAs($admin)->get(route($routeName));

            $response
                ->assertOk()
                ->assertSee('aria-label="Menu admin"', false)
                ->assertSee('href="'.route($routeName).'"', false)
                ->assertSee($label);

            $this->assertMatchesRegularExpression(
                '/href="'.preg_quote(route($routeName), '/').'"[^>]*aria-current="page"/s',
                $response->getContent(),
            );
        }
    }

    public function test_admin_menu_contains_all_destinations_once(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        foreach ($this->menuRoutes() as $routeName) {
            $response->assertSee('href="'.route($routeName).'"', false);
        }
    }

    public function test_nested_admin_page_keeps_its_parent_menu_current(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->get(route('admin.products.create'));

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.products.index'), '/').'"[^>]*aria-current="page"/s',
            $response->getContent(),
        );
    }

    public function test_reports_menu_exposes_both_available_exports(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('href="'.route('admin.reports.orders').'"', false)
            ->assertSee('href="'.route('admin.reports.products').'"', false);
    }

    public function test_guest_is_sent_to_admin_login_and_customer_is_forbidden(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));

        $customer = User::factory()->create();
        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }
}
