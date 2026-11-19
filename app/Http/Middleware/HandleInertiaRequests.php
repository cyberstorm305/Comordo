<?php

namespace App\Http\Middleware;

use App\Support\Seo;
use App\Support\Site;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'site' => Site::key(),
            'flash' => [
                'status' => $request->session()->get('status'),
            ],
            // The tab title on Inertia navigation; the server-rendered tags come from the same place.
            'seo' => fn () => ['title' => Seo::for($request)['title']],
        ];
    }
}
