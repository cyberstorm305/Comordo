<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

if (! defined('COMORDO_STATIC')) {
    define('COMORDO_STATIC', true);
}

require_once __DIR__.'/../../resources/static-site/_lib.php';

class StaticSiteEndpointsTest extends TestCase
{
    private const FAIL = "We couldn't send your message just now. Please email hello@comordo.com directly.";

    private function valid(array $over = []): array
    {
        return array_merge([
            'name' => 'Sinéad O\'Brien',
            'email' => 'sinead@example.org',
            'org' => 'Example School',
            'topic' => 'Data protection & security',
            'message' => 'Hello <b>&</b>',
            'website' => '',
        ], $over);
    }

    private array $dirs = [];

    private function tmpDir(): string
    {
        $d = sys_get_temp_dir().'/comordo-test-'.bin2hex(random_bytes(4));
        mkdir($d);
        $this->dirs[] = $d;

        return $d;
    }

    protected function tearDown(): void
    {
        foreach ($this->dirs as $d) {
            array_map('unlink', glob($d.'/*') ?: []);
            @rmdir($d);
        }
        $this->dirs = [];
    }

    public function test_validation_matches_the_laravel_form(): void
    {
        $this->assertSame([], comordo_validate_contact($this->valid()));
        $this->assertArrayHasKey('name', comordo_validate_contact($this->valid(['name' => ''])));
        $this->assertArrayHasKey('email', comordo_validate_contact($this->valid(['email' => 'nope'])));
        $this->assertArrayHasKey('topic', comordo_validate_contact($this->valid(['topic' => 'Other'])));
        $e = comordo_validate_contact($this->valid(['message' => str_repeat('a', 5001)]));
        $this->assertArrayHasKey('message', $e);
        $this->assertSame("That's too long.", $e['message']);
        $this->assertSame('Please add your name.', comordo_validate_contact($this->valid(['name' => '']))['name']);
    }

    public function test_organisation_over_190_characters_is_rejected(): void
    {
        $this->assertSame([], comordo_validate_contact($this->valid(['org' => str_repeat('a', 190)])));
        $e = comordo_validate_contact($this->valid(['org' => str_repeat('a', 191)]));
        $this->assertSame("That's too long.", $e['org']);
    }

    public function test_contact_email_is_plain_text_and_unescaped(): void
    {
        $m = comordo_contact_email($this->valid());
        $this->assertSame('Contact form (comordo.com): Data protection & security', $m['Subject']);
        $this->assertSame('sinead@example.org', $m['ReplyTo']);
        $this->assertSame('hello@comordo.com', $m['To']);
        $this->assertSame('Comordo <hello@comordo.com>', $m['From']);
        $this->assertSame('outbound', $m['MessageStream']);
        $this->assertStringContainsString("Sinéad O'Brien", $m['TextBody']);
        $this->assertStringContainsString('<b>&</b>', $m['TextBody']);
        $this->assertStringNotContainsString('&amp;', $m['TextBody']);
    }

    public function test_rate_limit_allows_five_then_blocks(): void
    {
        $d = $this->tmpDir();
        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse(comordo_rate_limited($d, '203.0.113.1'));
        }
        $this->assertTrue(comordo_rate_limited($d, '203.0.113.1'));
        $this->assertFalse(comordo_rate_limited($d, '203.0.113.2'));
    }

    public function test_client_ip_prefers_cloudflare_header_when_valid(): void
    {
        $this->assertSame('198.51.100.7', comordo_client_ip(['HTTP_CF_CONNECTING_IP' => '198.51.100.7', 'REMOTE_ADDR' => '10.0.0.1']));
        $this->assertSame('10.0.0.1', comordo_client_ip(['HTTP_CF_CONNECTING_IP' => 'garbage', 'REMOTE_ADDR' => '10.0.0.1']));
        $this->assertSame('10.0.0.1', comordo_client_ip(['REMOTE_ADDR' => '10.0.0.1']));
    }

    public function test_missing_config_returns_null(): void
    {
        $d = $this->tmpDir();
        $this->assertNull(comordo_config($d.'/nope.php'));
        file_put_contents($d.'/c.php', '<?php return "x";');
        $this->assertNull(comordo_config($d.'/c.php'));
        file_put_contents($d.'/ok.php', '<?php return ["postmark_token" => "t"];');
        $this->assertSame(['postmark_token' => 't'], comordo_config($d.'/ok.php'));
    }

    public function test_mailchimp_member_request(): void
    {
        $r = comordo_mailchimp_member('Foo@Example.ORG', ['api_key' => 'abc-us21', 'audience_id' => 'aud1', 'tags' => ['comordo.com']]);
        $this->assertSame('https://us21.api.mailchimp.com/3.0/lists/aud1/members/'.md5('foo@example.org'), $r['url']);
        $this->assertSame('anystring:abc-us21', $r['auth']);
        $this->assertSame('subscribed', $r['payload']['status']);
        $this->assertSame('subscribed', $r['payload']['status_if_new']);
        $this->assertSame('Foo@Example.ORG', $r['payload']['email_address']);
        $this->assertSame(['comordo.com'], $r['payload']['tags']);
    }

    public function test_contact_mail_failure_returns_error(): void
    {
        $cfg = ['postmark_token' => 'tok', 'data_dir' => $this->tmpDir()];
        $http = fn () => ['status' => 0, 'body' => null];
        $r = comordo_handle_contact($this->valid(), ['REMOTE_ADDR' => '203.0.113.9'], $cfg, $http);
        $this->assertSame(502, $r['status']);
        $this->assertSame(['ok' => false, 'message' => self::FAIL], $r['body']);

        $r = comordo_handle_contact($this->valid(), ['REMOTE_ADDR' => '203.0.113.9'], ['data_dir' => $cfg['data_dir']], $http);
        $this->assertSame(502, $r['status']);
        $r = comordo_handle_contact($this->valid(), [], null, $http);
        $this->assertSame(502, $r['status']);
        $r = comordo_handle_contact($this->valid(), [], $cfg, fn () => ['status' => 422, 'body' => []]);
        $this->assertSame(502, $r['status']);
    }

    public function test_contact_success_validation_honeypot_and_rate_limit(): void
    {
        $cfg = ['postmark_token' => 'tok', 'data_dir' => $this->tmpDir()];
        $calls = [];
        $http = function ($m, $u, $h, $b) use (&$calls) {
            $calls[] = [$m, $u, $h, $b];

            return ['status' => 200, 'body' => []];
        };
        $s = ['REMOTE_ADDR' => '203.0.113.5'];
        $r = comordo_handle_contact($this->valid(), $s, $cfg, $http);
        $this->assertSame(['status' => 200, 'body' => ['ok' => true]], $r);
        $this->assertSame('POST', $calls[0][0]);
        $this->assertSame('https://api.postmarkapp.com/email', $calls[0][1]);
        $this->assertContains('X-Postmark-Server-Token: tok', $calls[0][2]);

        $r = comordo_handle_contact($this->valid(['name' => '']), $s, $cfg, $http);
        $this->assertSame(422, $r['status']);
        $this->assertArrayHasKey('name', $r['body']['errors']);

        $n = count($calls);
        $r = comordo_handle_contact($this->valid(['website' => 'spam']), $s, $cfg, $http);
        $this->assertSame(['status' => 200, 'body' => ['ok' => true]], $r);
        $this->assertCount($n, $calls);

        for ($i = 0; $i < 6; $i++) {
            $r = comordo_handle_contact($this->valid(), ['REMOTE_ADDR' => '203.0.113.6'], $cfg, $http);
        }
        $this->assertSame(429, $r['status']);
        $this->assertSame('Too many messages — please wait a minute and try again.', $r['body']['message']);

        // Unwritable data_dir does not block sending.
        $r = comordo_handle_contact($this->valid(), ['REMOTE_ADDR' => '203.0.113.7'], ['postmark_token' => 'tok', 'data_dir' => '/nonexistent/x'], $http);
        $this->assertSame(200, $r['status']);
    }

    public function test_subscribe_handler(): void
    {
        $cfg = ['mailchimp' => ['api_key' => 'abc-us21', 'audience_id' => 'aud1', 'tags' => ['t']]];
        $ok = fn () => ['status' => 200, 'body' => []];

        $r = comordo_handle_subscribe(['email' => 'a@b.co', 'consent' => true], $cfg, $ok);
        $this->assertSame(['status' => 200, 'body' => ['ok' => true]], $r);

        $r = comordo_handle_subscribe(['email' => 'bad', 'consent' => true], $cfg, $ok);
        $this->assertSame([422, 'Please enter a valid email address so we can reach you.'], [$r['status'], $r['body']['message']]);

        $r = comordo_handle_subscribe(['email' => 'a@b.co'], $cfg, $ok);
        $this->assertSame([422, 'Please tick the box to confirm you’re happy for us to contact you.'], [$r['status'], $r['body']['message']]);

        foreach ([null, [], ['mailchimp' => ['api_key' => 'nodash', 'audience_id' => 'x']]] as $bad) {
            $r = comordo_handle_subscribe(['email' => 'a@b.co', 'consent' => true], $bad, $ok);
            $this->assertSame([500, 'Newsletter signup is not configured yet.'], [$r['status'], $r['body']['message']]);
        }

        $r = comordo_handle_subscribe(['email' => 'a@b.co', 'consent' => true], $cfg, fn () => ['status' => 400, 'body' => []]);
        $this->assertSame([502, 'We couldn’t register that email right now. Please try again shortly.'], [$r['status'], $r['body']['message']]);
    }

    public function test_lib_cannot_be_requested_directly(): void
    {
        $out = shell_exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/../../resources/static-site/_lib.php'));
        $this->assertSame('', (string) $out);
    }

    public function test_broken_config_returns_null(): void
    {
        $d = $this->tmpDir();
        file_put_contents($d.'/bad.php', '<?php return [ "a" => ;');
        $this->assertNull(comordo_config($d.'/bad.php'));
        file_put_contents($d.'/throws.php', '<?php throw new RuntimeException("x");');
        $this->assertNull(comordo_config($d.'/throws.php'));
    }

    public function test_honeypot_zero_is_fake_success_without_sending(): void
    {
        $called = false;
        $http = function () use (&$called) {
            $called = true;

            return ['status' => 200, 'body' => []];
        };
        $r = comordo_handle_contact($this->valid(['website' => '0']), [], ['postmark_token' => 't'], $http);
        $this->assertSame(['status' => 200, 'body' => ['ok' => true]], $r);
        $this->assertFalse($called);
    }

    public function test_non_array_tags_become_empty(): void
    {
        $r = comordo_mailchimp_member('a@b.co', ['api_key' => 'k-us1', 'audience_id' => 'a', 'tags' => 'oops']);
        $this->assertSame([], $r['payload']['tags']);
    }

    public function test_empty_datacentre_suffix_is_not_configured(): void
    {
        $r = comordo_handle_subscribe(['email' => 'a@b.co', 'consent' => true], ['mailchimp' => ['api_key' => 'abc-', 'audience_id' => 'x']], fn () => ['status' => 200, 'body' => []]);
        $this->assertSame([500, 'Newsletter signup is not configured yet.'], [$r['status'], $r['body']['message']]);
    }
}
