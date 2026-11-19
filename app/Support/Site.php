<?php

namespace App\Support;

class Site
{
    public const SITES = ['imagesafe', 'comordo'];

    /** The site being served; anything unrecognised falls back to ImageSafe. */
    public static function key(): string
    {
        $key = config('site.key');

        return in_array($key, self::SITES, true) ? $key : 'imagesafe';
    }

    public static function is(string $key): bool
    {
        return self::key() === $key;
    }
}
