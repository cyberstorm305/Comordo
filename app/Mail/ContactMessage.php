<?php

namespace App\Mail;

use App\Support\Site;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactMessage extends Mailable
{
    public readonly string $site;

    /** @param array{name: string, email: string, org?: ?string, topic: string, message?: ?string} $data */
    public function __construct(public readonly array $data)
    {
        $this->site = Site::is('comordo') ? 'comordo.com' : 'imagesafe.co.uk';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->data['email'], $this->data['name'])],
            subject: "Contact form ({$this->site}): {$this->data['topic']}",
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact');
    }
}
