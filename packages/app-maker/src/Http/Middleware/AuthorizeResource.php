<?php

namespace AppMaker\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeResource
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // This middleware can be used for additional authorization logic
        // Currently, authorization is handled in the ResourceController

        return $next($request);
    }
}
