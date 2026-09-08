<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanySettings
{
    /**
     * @var array<string, array{group: string, label: string, help: string, type: string, rules: array<int, string>}>
     */
    private const DEFINITIONS = [
        'company_name' => ['group' => 'Identitas perusahaan', 'label' => 'Nama brand', 'help' => 'Nama pendek yang tampil pada logo dan judul situs.', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
        'brand_suffix' => ['group' => 'Identitas perusahaan', 'label' => 'Teks di bawah logo', 'help' => 'Contoh: Global Supply.', 'type' => 'text', 'rules' => ['required', 'string', 'max:80']],
        'legal_name' => ['group' => 'Identitas perusahaan', 'label' => 'Nama badan usaha', 'help' => 'Nama legal perusahaan.', 'type' => 'text', 'rules' => ['required', 'string', 'max:150']],
        'tagline' => ['group' => 'Identitas perusahaan', 'label' => 'Tagline', 'help' => 'Kalimat pendek yang mewakili perusahaan.', 'type' => 'text', 'rules' => ['required', 'string', 'max:180']],
        'business_summary' => ['group' => 'Identitas perusahaan', 'label' => 'Ringkasan perusahaan', 'help' => 'Penjelasan singkat untuk Tentang Kami dan metadata situs.', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:700']],
        'nib' => ['group' => 'Legalitas', 'label' => 'NIB', 'help' => 'Nomor Induk Berusaha yang boleh ditampilkan publik.', 'type' => 'text', 'rules' => ['required', 'string', 'max:40']],
        'nib_issued_at' => ['group' => 'Legalitas', 'label' => 'Tanggal penerbitan NIB', 'help' => 'Tanggal penerbitan seperti yang tercantum pada dokumen OSS.', 'type' => 'text', 'rules' => ['required', 'string', 'max:80']],
        'legal_status' => ['group' => 'Legalitas', 'label' => 'Status usaha', 'help' => 'Contoh: PMDN · Usaha Mikro.', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
        'location' => ['group' => 'Kontak', 'label' => 'Domisili publik', 'help' => 'Gunakan kota/kabupaten; hindari alamat rumah lengkap.', 'type' => 'text', 'rules' => ['required', 'string', 'max:200']],
        'email' => ['group' => 'Kontak', 'label' => 'Email perusahaan', 'help' => 'Alamat email publik untuk pelanggan dan mitra.', 'type' => 'email', 'rules' => ['required', 'email', 'max:160']],
        'phone' => ['group' => 'Kontak', 'label' => 'Nomor telepon', 'help' => 'Nomor yang ditampilkan kepada pengunjung.', 'type' => 'text', 'rules' => ['required', 'string', 'max:30']],
        'whatsapp' => ['group' => 'Kontak', 'label' => 'Nomor WhatsApp', 'help' => 'Format internasional tanpa tanda +, contoh 6281234567890.', 'type' => 'text', 'rules' => ['required', 'regex:/^[0-9]{8,20}$/']],
        'operating_hours' => ['group' => 'Kontak', 'label' => 'Jam layanan', 'help' => 'Hari dan jam layanan pelanggan.', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
        'hero_eyebrow' => ['group' => 'Homepage', 'label' => 'Label hero', 'help' => 'Teks kecil di atas headline homepage.', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
        'hero_title' => ['group' => 'Homepage', 'label' => 'Headline hero', 'help' => 'Judul utama pada homepage.', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:220']],
        'hero_description' => ['group' => 'Homepage', 'label' => 'Deskripsi hero', 'help' => 'Penjelasan singkat di bawah headline.', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:350']],
        'show_testimonials' => ['group' => 'Homepage', 'label' => 'Tampilkan testimoni', 'help' => 'Mengaktifkan bagian testimoni pilihan pada homepage.', 'type' => 'boolean', 'rules' => ['required', 'in:0,1']],
        'show_faq' => ['group' => 'Homepage', 'label' => 'Tampilkan FAQ', 'help' => 'Mengaktifkan bagian FAQ pilihan pada homepage.', 'type' => 'boolean', 'rules' => ['required', 'in:0,1']],
    ];

    /** @var array<string, string>|null */
    private ?array $values = null;

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $defaults = config('novastra.company', []);

        if (! Schema::hasTable('site_settings')) {
            return $this->values = $defaults;
        }

        $stored = SiteSetting::query()
            ->whereIn('key', array_keys(self::DEFINITIONS))
            ->pluck('value', 'key')
            ->all();

        return $this->values = array_replace($defaults, $stored);
    }

    /**
     * @return array<string, array{group: string, label: string, help: string, type: string, rules: array<int, string>}>
     */
    public function definitions(): array
    {
        return self::DEFINITIONS;
    }

    /**
     * @param  array<string, string>  $values
     */
    public function update(array $values): void
    {
        DB::transaction(function () use ($values): void {
            foreach (array_keys(self::DEFINITIONS) as $key) {
                SiteSetting::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => trim($values[$key])],
                );
            }
        });

        $this->values = null;
    }

    public function reset(): void
    {
        SiteSetting::query()->whereIn('key', array_keys(self::DEFINITIONS))->delete();
        $this->values = null;
    }
}
