<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:160'], 'message' => ['required', 'string', 'max:1000']]);
        Mail::raw($data['message'], fn ($mail) => $mail->replyTo($data['email'], $data['name'])->to(config('mail.from.address'))->subject('Pesan kontak Novastra'));

        return back()->with('success', 'Pesan sudah dikirim.');
    }
}
