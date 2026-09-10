<?php

namespace App\Http\Controllers;

use App\Services\CompanySettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request, CompanySettings $settings): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:160'], 'message' => ['required', 'string', 'max:1000']]);
        $company = $settings->all();

        try {
            Mail::raw($data['message'], fn ($mail) => $mail->replyTo($data['email'], $data['name'])->to($company['email'])->subject('Pesan kontak '.$company['company_name']));
        } catch (Throwable $error) {
            Log::warning('Contact email failed', ['error' => $error->getMessage()]);

            return back()->withInput()->withErrors(['contact' => 'Pesan belum dapat dikirim. Silakan coba kembali atau hubungi kami melalui WhatsApp.']);
        }

        return back()->with('success', 'Pesan sudah dikirim.');
    }
}
