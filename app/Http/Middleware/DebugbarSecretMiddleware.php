<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DebugbarSecretMiddleware {
    public function handle(Request $request, Closure $next) {
        if (config('debugbar.enabled')) {
            $secret = config('debugbar.secret');
            $providedSecret = $request->query('debugbar');

            if (empty($secret) || $providedSecret !== $secret) {
                if (class_exists(\Barryvdh\Debugbar\Facades\Debugbar::class)) {
                    \Barryvdh\Debugbar\Facades\Debugbar::disable();
                }
            }
        }

        return $next($request);
    }
}
