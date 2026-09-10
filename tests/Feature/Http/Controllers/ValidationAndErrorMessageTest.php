<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Feedback;
use App\Models\Order;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ValidationAndErrorMessageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_log_in_when_username_has_spaces_or_uppercase_letters(): void
    {
        $admin = User::factory()->create(['username' => 'admin-toko', 'role' => 'ADMIN']);

        $this->post(route('admin.login.store'), [
            'username' => '  ADMIN-TOKO  ',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_customer_validation_messages_are_clear_and_all_are_displayed(): void
    {
        $response = $this->followingRedirects()->from(route('register'))->post(route('register.store'), [
            'name' => 'A',
            'username' => 'nama tidak valid',
            'email' => 'bukan-email',
            'password' => 'pendek',
            'password_confirmation' => 'berbeda',
        ]);

        $response->assertOk()
            ->assertSee('Periksa kembali informasi berikut:')
            ->assertSee('Nama harus memiliki minimal 2 karakter.')
            ->assertSee('Username hanya boleh berisi huruf kecil, angka, titik, garis bawah, dan tanda hubung.')
            ->assertSee('Masukkan alamat email yang valid.')
            ->assertSee('Konfirmasi kata sandi tidak cocok.')
            ->assertDontSee('validation.');
    }

    public function test_customer_receives_feedback_timing_message_instead_of_error_page(): void
    {
        $customer = User::factory()->create(['role' => 'CUSTOMER']);
        $order = Order::factory()->for($customer, 'customer')->create(['status' => 'PROCESSING']);

        $this->actingAs($customer)
            ->from(route('orders.show', $order))
            ->post(route('orders.feedback', $order), ['rating' => 5, 'message' => 'Pelayanan sangat baik.'])
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHasErrors(['feedback' => 'Masukan dapat dikirim setelah pesanan selesai.']);

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_admin_validation_messages_are_clear_and_all_are_displayed(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->followingRedirects()
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => '',
                'sku' => '',
                'category_id' => 999999,
                'short_description' => '',
                'description' => '',
                'price' => '-1',
                'stock' => -1,
                'status' => 'UNKNOWN',
            ]);

        $response->assertOk()
            ->assertSee('Periksa kembali informasi berikut:')
            ->assertSee('Kolom nama wajib diisi.')
            ->assertSee('Kolom SKU wajib diisi.')
            ->assertSee('Pilihan kategori tidak tersedia.')
            ->assertSee('Nilai harga minimal 0.')
            ->assertSee('Nilai stok minimal 0.')
            ->assertSee('Pilihan status tidak valid.')
            ->assertDontSee('validation.');
    }

    public function test_duplicate_testimonial_source_redirects_with_actionable_message(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $feedback = Feedback::create([
            'source' => 'WHATSAPP',
            'name' => 'Pelanggan',
            'email' => 'pelanggan@example.com',
            'rating' => 5,
            'message' => 'Sangat baik.',
        ]);
        Testimonial::create([
            'feedback_id' => $feedback->id,
            'display_name' => 'Pelanggan',
            'rating' => 5,
            'content' => 'Sangat baik.',
            'status' => 'PUBLISHED',
            'featured' => false,
            'sort_order' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.testimonials.create', ['feedback' => $feedback->id]))
            ->assertRedirect(route('admin.testimonials.index'))
            ->assertSessionHasErrors(['feedback_id' => 'Feedback ini sudah digunakan sebagai testimoni.']);
    }

    public function test_standard_error_pages_explain_the_problem_in_indonesian(): void
    {
        $this->view('errors.403')->assertSee('Akses tidak diizinkan');
        $this->view('errors.404')->assertSee('Halaman tidak ditemukan');
        $this->view('errors.419')->assertSee('Sesi telah berakhir');
        $this->view('errors.429')->assertSee('Terlalu banyak percobaan');
        $this->view('errors.500')->assertSee('Terjadi kendala pada sistem');
        $this->view('errors.503')->assertSee('Layanan sedang tidak tersedia');
    }
}
