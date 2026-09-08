<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Faq;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\CompanySettings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TrustContentControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $privateUploadDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->privateUploadDirectory = storage_path('framework/testing/private-'.uniqid());
        config(['novastra.private_upload_dir' => $this->privateUploadDirectory]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->privateUploadDirectory);
        parent::tearDown();
    }

    public function test_existing_customer_feedback_flow_stays_website_only_and_immutable_to_admin(): void
    {
        $customer = User::factory()->create(['role' => 'CUSTOMER']);
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $order = Order::factory()->for($customer, 'customer')->create(['status' => 'COMPLETED']);

        $this->actingAs($customer)->post(route('orders.feedback', $order), [
            'rating' => 5,
            'message' => 'Pesanan tiba segar dan tepat waktu.',
        ])->assertRedirect();

        $feedback = Feedback::firstOrFail();
        $this->assertSame('WEBSITE', $feedback->source);

        $this->actingAs($admin)->get(route('admin.feedback.edit', $feedback))->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.feedback.destroy', $feedback))->assertForbidden();
        $this->assertModelExists($feedback);
    }

    public function test_admin_can_add_whatsapp_feedback_with_private_screenshot(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)->post(route('admin.feedback.store'), [
            'name' => 'Pelanggan WhatsApp',
            'rating' => 5,
            'message' => 'Ayamnya segar dan pengiriman cepat.',
            'occurred_at' => '2026-09-09',
            'screenshot' => UploadedFile::fake()->image('whatsapp.jpg', 600, 900),
        ])->assertRedirect(route('admin.feedback.index'));

        $feedback = Feedback::firstOrFail();
        $this->assertSame('WHATSAPP', $feedback->source);
        $this->assertNotNull($feedback->screenshot_path);
        $this->assertFileExists($this->privateUploadDirectory.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $feedback->screenshot_path));
        $this->actingAs($admin)->get(route('admin.feedback.screenshot', $feedback))->assertOk();

        $customer = User::factory()->create(['role' => 'CUSTOMER']);
        $this->actingAs($customer)->get(route('admin.feedback.screenshot', $feedback))->assertForbidden();
    }

    public function test_only_published_featured_testimonials_appear_on_homepage(): void
    {
        Testimonial::create(['display_name' => 'Ayu', 'rating' => 5, 'content' => 'Testimoni yang tampil.', 'status' => 'PUBLISHED', 'featured' => true, 'sort_order' => 1]);
        Testimonial::create(['display_name' => 'Budi', 'rating' => 4, 'content' => 'Testimoni yang disembunyikan.', 'status' => 'HIDDEN', 'featured' => true, 'sort_order' => 2]);
        Testimonial::create(['display_name' => 'Citra', 'rating' => 5, 'content' => 'Bukan pilihan homepage.', 'status' => 'PUBLISHED', 'featured' => false, 'sort_order' => 3]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Testimoni yang tampil.')
            ->assertDontSee('Testimoni yang disembunyikan.')
            ->assertDontSee('Bukan pilihan homepage.');
    }

    public function test_admin_can_curate_feedback_into_a_testimonial(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $feedback = Feedback::create([
            'source' => 'WHATSAPP',
            'name' => 'Nama Lengkap Pelanggan',
            'rating' => 5,
            'message' => 'Bahan datang dalam kondisi sangat segar.',
        ]);

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'feedback_id' => $feedback->id,
            'display_name' => 'N. Pelanggan',
            'customer_type' => 'Pemilik katering',
            'rating' => 5,
            'content' => 'Bahan datang dalam kondisi sangat segar.',
            'status' => 'PUBLISHED',
            'featured' => '1',
            'sort_order' => 1,
        ])->assertRedirect(route('admin.testimonials.index'));

        $this->assertDatabaseHas('testimonials', ['feedback_id' => $feedback->id, 'display_name' => 'N. Pelanggan', 'featured' => true]);
        $this->get(route('home'))->assertSee('N. Pelanggan')->assertDontSee('Nama Lengkap Pelanggan');

        $this->actingAs($admin)
            ->delete(route('admin.feedback.destroy', $feedback))
            ->assertSessionHasErrors(['feedback' => 'Feedback tidak dapat dihapus karena masih menjadi sumber testimoni. Hapus testimoni terlebih dahulu.']);
        $this->assertModelExists($feedback);
    }

    public function test_faq_visibility_and_global_homepage_controls_are_respected(): void
    {
        Faq::create(['question' => 'FAQ homepage?', 'answer' => 'Jawaban homepage.', 'is_active' => true, 'show_on_home' => true, 'sort_order' => 1]);
        Faq::create(['question' => 'FAQ halaman saja?', 'answer' => 'Jawaban halaman.', 'is_active' => true, 'show_on_home' => false, 'sort_order' => 2]);
        Faq::create(['question' => 'FAQ nonaktif?', 'answer' => 'Tidak boleh terlihat.', 'is_active' => false, 'show_on_home' => true, 'sort_order' => 3]);

        $this->get(route('home'))->assertSee('FAQ homepage?')->assertDontSee('FAQ halaman saja?')->assertDontSee('FAQ nonaktif?');
        $this->get(route('faq'))->assertSee('FAQ homepage?')->assertSee('FAQ halaman saja?')->assertDontSee('FAQ nonaktif?');

        SiteSetting::query()->updateOrCreate(['key' => 'show_faq'], ['value' => '0']);
        $this->app->forgetInstance(CompanySettings::class);
        $this->get(route('home'))->assertDontSee('FAQ homepage?');
    }

    public function test_admin_can_create_update_and_delete_an_faq(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)->post(route('admin.faqs.store'), [
            'question' => 'Apakah bisa kirim terjadwal?',
            'answer' => 'Bisa dibicarakan dengan tim kami.',
            'sort_order' => 2,
            'is_active' => '1',
            'show_on_home' => '1',
        ])->assertRedirect(route('admin.faqs.index'));

        $faq = Faq::firstOrFail();
        $this->actingAs($admin)->put(route('admin.faqs.update', $faq), [
            'question' => 'Apakah tersedia pengiriman terjadwal?',
            'answer' => 'Ya, silakan diskusikan jadwal dengan tim kami.',
            'sort_order' => 1,
            'is_active' => '1',
        ])->assertRedirect(route('admin.faqs.index'));

        $this->assertDatabaseHas('faqs', ['id' => $faq->id, 'show_on_home' => false]);
        $this->actingAs($admin)->delete(route('admin.faqs.destroy', $faq))->assertRedirect(route('admin.faqs.index'));
        $this->assertModelMissing($faq);
    }
}
