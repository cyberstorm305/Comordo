<?php

namespace App\Http\Middleware;

use App\Support\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSite
{
    /** 404 unless the deployment serves the given site, e.g. ->middleware('site:imagesafe'). */
    public function handle(Request $request, Closure $next, string $site): Response
    {
        abort_unless(Site::is($site), 404);

        return $next($request);
    }
}
