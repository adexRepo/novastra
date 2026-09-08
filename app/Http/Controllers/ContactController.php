<?php

namespace App\Http\Controllers;

use App\Services\CompanySettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request, CompanySettings $settings)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:160'], 'message' => ['required', 'string', 'max:1000']]);
        $company = $settings->all();
        Mail::raw($data['message'], fn ($mail) => $mail->replyTo($data['email'], $data['name'])->to($company['email'])->subject('Pesan kontak '.$company['company_name']));

        return back()->with('success', 'Pesan sudah dikirim.');
    }
}
