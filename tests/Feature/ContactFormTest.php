<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    private function valid(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Smith',
            'email' => 'jane@example.org',
            'org' => 'Meridian Learning Trust',
            'topic' => 'ImageSafe',
            'message' => 'Hello there.',
            'website' => '',
        ], $overrides);
    }

    public function test_a_valid_message_is_emailed_to_hello(): void
    {
        Mail::fake();

        $this->post('/contact', $this->valid())
            ->assertRedirect('/contact')
            ->assertSessionHas('status', 'contact-sent');

        Mail::assertSent(ContactMessage::class, fn (ContactMessage $m) => $m->hasTo('hello@comordo.com')
            && $m->hasReplyTo('jane@example.org'));
    }

    public function test_validation_errors(): void
    {
        Mail::fake();

        $this->post('/contact', $this->valid(['name' => '', 'email' => 'nope']))
            ->assertSessionHasErrors(['name', 'email']);

        Mail::assertNothingSent();
    }

    public function test_honeypot_submissions_look_successful_but_send_nothing(): void
    {
        Mail::fake();

        $this->post('/contact', $this->valid(['website' => 'http://spam.example']))
            ->assertRedirect('/contact')
            ->assertSessionHas('status', 'contact-sent');

        Mail::assertNothingSent();
    }

    public function test_rate_limited_after_five_posts(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $this->valid())->assertRedirect('/contact');
        }

        $this->post('/contact', $this->valid())->assertStatus(429);
    }

    public function test_mail_failure_tells_the_visitor_to_email_us(): void
    {
        Mail::shouldReceive('to')->andThrow(new TransportException('down'));

        $this->post('/contact', $this->valid())
            ->assertRedirect()
            ->assertSessionHasErrors(['form' => "We couldn't send your message just now. Please email hello@comordo.com directly."])
            ->assertSessionMissing('status');
    }

    public function test_email_says_which_site_it_came_from(): void
    {
        config(['site.key' => 'comordo']);
        $this->assertSame('Contact form (comordo.com): ImageSafe', (new ContactMessage($this->valid()))->envelope()->subject);

        config(['site.key' => 'imagesafe']);
        $this->assertSame('Contact form (imagesafe.co.uk): ImageSafe', (new ContactMessage($this->valid()))->envelope()->subject);
    }

    public function test_an_invalid_topic_is_rejected(): void
    {
        Mail::fake();

        $this->post('/contact', $this->valid(['topic' => 'Something else']))
            ->assertSessionHasErrors(['topic']);

        Mail::assertNothingSent();
    }

    public function test_text_email_is_not_html_escaped(): void
    {
        $mailable = new ContactMessage($this->valid([
            'name' => "Sinéad O'Brien",
            'topic' => 'Data protection & security',
            'message' => '<b>&</b>',
        ]));

        $mailable->assertSeeInText("Sinéad O'Brien");
        $mailable->assertSeeInText('Topic: Data protection & security');
        $mailable->assertSeeInText('<b>&</b>');
        $this->assertSame('Contact form (imagesafe.co.uk): Data protection & security', $mailable->envelope()->subject);
    }
}
