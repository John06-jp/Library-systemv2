<?php

namespace App\Http\Middleware;

use App\Services\Auth\ModuleAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeveloper
{
    public function __construct(private readonly ModuleAccessService $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($this->access->isDeveloper($request->user()), 403, 'Forbidden');

        $request->session()->put('active_module', 'developer');

        return $next($request);
    }
}
