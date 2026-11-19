<?php

declare(strict_types=1);

if (!defined('COMORDO_STATIC')) {
    http_response_code(404);
    exit;
}

const COMORDO_TOPICS = ['General enquiry', 'ImageSafe', 'Data protection & security', 'Billing & licences', 'Press'];
const COMORDO_CONTACT_FAILURE = "We couldn't send your message just now. Please email hello@comordo.com directly.";

function comordo_config(string $path): ?array
{
    if (!is_file($path)) {
        return null;
    }
    try {
        $config = (static fn () => include $path)();
    } catch (Throwable $e) {
        error_log('comordo config unreadable: ' . $e->getMessage());

        return null;
    }

    return is_array($config) ? $config : null;
}

function comordo_validate_contact(array $in): array
{
    $str = static fn (string $k): string => is_string($in[$k] ?? null) ? trim($in[$k]) : '';
    $errors = [];

    $name = $str('name');
    if ($name === '') {
        $errors['name'] = 'Please add your name.';
    } elseif (mb_strlen($name) > 120) {
        $errors['name'] = "That's too long.";
    }

    $email = $str('email');
    if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Please add a valid email address.';
    } elseif (mb_strlen($email) > 190) {
        $errors['email'] = "That's too long.";
    }

    if (mb_strlen($str('organisation')) > 190) {
        $errors['organisation'] = "That's too long.";
    }

    if (!in_array($str('topic'), COMORDO_TOPICS, true)) {
        $errors['topic'] = 'Please choose a topic.';
    }

    if (mb_strlen($str('message')) > 5000) {
        $errors['message'] = "That's too long.";
    }

    return $errors;
}

function comordo_client_ip(array $server): string
{
    $cf = $server['HTTP_CF_CONNECTING_IP'] ?? null;
    if (is_string($cf) && filter_var($cf, FILTER_VALIDATE_IP) !== false) {
        return $cf;
    }

    return is_string($server['REMOTE_ADDR'] ?? null) ? $server['REMOTE_ADDR'] : '0.0.0.0';
}

/** Records a hit and returns true when this IP has already used its allowance. */
function comordo_rate_limited(string $dataDir, string $ip, int $max = 5, int $window = 60): bool
{
    $file = rtrim($dataDir, '/') . '/rl-' . hash('sha256', $ip) . '.json';
    $fh = @fopen($file, 'c+');
    if ($fh === false) {
        throw new RuntimeException('rate limit file not writable');
    }
    try {
        flock($fh, LOCK_EX);
        $times = json_decode((string) stream_get_contents($fh), true);
        $now = time();
        $times = array_values(array_filter(is_array($times) ? $times : [], static fn ($t) => is_int($t) && $t > $now - $window));
        $limited = count($times) >= $max;
        if (!$limited) {
            $times[] = $now;
        }
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($times));
        flock($fh, LOCK_UN);
    } finally {
        fclose($fh);
    }

    return $limited;
}

function comordo_contact_email(array $in): array
{
    $v = static fn (string $k): string => is_string($in[$k] ?? null) ? trim($in[$k]) : '';
    $topic = $v('topic');
    $org = $v('organisation');

    return [
        'From' => 'Comordo <hello@comordo.com>',
        'To' => 'hello@comordo.com',
        'ReplyTo' => $v('email'),
        'Subject' => 'Contact form (comordo.com): ' . $topic,
        'TextBody' => implode("\n", [
            'Name: ' . $v('name'),
            'Email: ' . $v('email'),
            'Organisation: ' . ($org !== '' ? $org : '-'),
            'Topic: ' . $topic,
            '',
            $v('message'),
            '',
            'Sent from the comordo.com contact form.',
        ]),
        'MessageStream' => 'outbound',
    ];
}

/** @return array{status:int, body:mixed} status is 0 on transport failure */
function comordo_http_json(string $method, string $url, array $headers, array $body, int $timeout = 10): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => $timeout,
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    if ($raw === false) {
        return ['status' => 0, 'body' => null];
    }

    return ['status' => $status, 'body' => json_decode((string) $raw, true)];
}

function comordo_mailchimp_member(string $email, array $mc): array
{
    $key = (string) ($mc['api_key'] ?? '');
    $dc = substr(strrchr($key, '-') ?: '', 1);
    $tags = is_array($mc['tags'] ?? null) ? array_values($mc['tags']) : [];
    $payload = [
        'email_address' => $email,
        'status_if_new' => 'subscribed',
        'status' => 'subscribed',
        'tags' => $tags,
    ];

    return [
        'url' => sprintf('https://%s.api.mailchimp.com/3.0/lists/%s/members/%s', $dc, $mc['audience_id'] ?? '', md5(strtolower($email))),
        'payload' => $payload,
        'auth' => 'anystring:' . $key,
    ];
}

function comordo_handle_contact(array $in, array $server, ?array $config, ?callable $http = null): array
{
    $http ??= 'comordo_http_json';
    $ip = comordo_client_ip($server);
    $dataDir = is_string($config['data_dir'] ?? null) ? $config['data_dir'] : '';

    try {
        if ($dataDir === '') {
            throw new RuntimeException('data_dir not configured');
        }
        if (comordo_rate_limited($dataDir, $ip)) {
            return ['status' => 429, 'body' => ['ok' => false, 'message' => 'Too many messages — please wait a minute and try again.']];
        }
    } catch (Throwable $e) {
        error_log('comordo contact: rate limiting skipped: ' . $e->getMessage());
    }

    if (trim((string) ($in['website'] ?? '')) !== '') {
        return ['status' => 200, 'body' => ['ok' => true]];
    }

    $errors = comordo_validate_contact($in);
    if ($errors !== []) {
        return ['status' => 422, 'body' => ['ok' => false, 'errors' => $errors]];
    }

    $fail = ['status' => 502, 'body' => ['ok' => false, 'message' => COMORDO_CONTACT_FAILURE]];
    $token = is_string($config['postmark_token'] ?? null) ? $config['postmark_token'] : '';
    if ($token === '') {
        error_log('comordo contact: postmark_token missing');

        return $fail;
    }

    $res = $http('POST', 'https://api.postmarkapp.com/email', [
        'Accept: application/json',
        'Content-Type: application/json',
        'X-Postmark-Server-Token: ' . $token,
    ], comordo_contact_email($in));

    if ($res['status'] < 200 || $res['status'] >= 300) {
        error_log('comordo contact: Postmark failed with status ' . $res['status']);

        return $fail;
    }

    return ['status' => 200, 'body' => ['ok' => true]];
}

function comordo_handle_subscribe(array $in, ?array $config, ?callable $http = null): array
{
    $http ??= 'comordo_http_json';
    $email = is_string($in['email'] ?? null) ? trim($in['email']) : '';
    $consent = filter_var($in['consent'] ?? false, FILTER_VALIDATE_BOOLEAN);

    if ($email === '' || strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return ['status' => 422, 'body' => ['ok' => false, 'message' => 'Please enter a valid email address.']];
    }
    if (!$consent) {
        return ['status' => 422, 'body' => ['ok' => false, 'message' => 'Please confirm you are happy for us to contact you.']];
    }

    $mc = is_array($config['mailchimp'] ?? null) ? $config['mailchimp'] : [];
    $key = is_string($mc['api_key'] ?? null) ? $mc['api_key'] : '';
    $audience = is_string($mc['audience_id'] ?? null) ? $mc['audience_id'] : '';
    if ($key === '' || $audience === '' || substr(strrchr($key, '-') ?: '', 1) === '') {
        error_log('comordo subscribe: mailchimp not configured');

        return ['status' => 500, 'body' => ['ok' => false, 'message' => 'Newsletter signup is not configured yet.']];
    }

    $req = comordo_mailchimp_member($email, $mc);
    $res = $http('PUT', $req['url'], [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($req['auth']),
    ], $req['payload']);

    if ($res['status'] < 200 || $res['status'] >= 300) {
        error_log('comordo subscribe: Mailchimp failed with status ' . $res['status']);

        return ['status' => 502, 'body' => ['ok' => false, 'message' => 'We could not register that email right now. Please try again shortly.']];
    }

    return ['status' => 200, 'body' => ['ok' => true]];
}
