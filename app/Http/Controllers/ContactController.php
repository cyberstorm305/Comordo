<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Bots fill the hidden field: look successful, send nothing.
        if (filled($data['website'] ?? null)) {
            return redirect('/contact')->with('status', 'contact-sent');
        }

        try {
            Mail::to('hello@comordo.com')->send(new ContactMessage($data));
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['form' => "We couldn't send your message just now. Please email hello@comordo.com directly."]);
        }

        return redirect('/contact')->with('status', 'contact-sent');
    }
}
