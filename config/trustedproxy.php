<?php

/*
 * Proxies whose X-Forwarded-* headers Laravel believes (read by Laravel's TrustProxies middleware).
 *
 * Unset (the default) on our own servers: nginx there passes the visitor's IP itself.
 * Set it where the app sits behind a proxy that does not, such as comordo.com's host
 * (Cloudflare → nginx → Apache): list the nginx proxy's IP and Cloudflare's ranges, comma
 * separated (CIDR allowed). Without it, Laravel sees http instead of https and every
 * visitor as the proxy, so the contact form's per-IP limit is shared by everyone.
 */

return [
    'proxies' => env('TRUSTED_PROXIES'),
];
