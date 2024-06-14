<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $AUTH_USER = 'ec1f94aa-161b-446a-afb0-2de29a52060e';
        $AUTH_PASS = 'fd0f38d4-46dd-44ce-b318-c208f7f0e6df';
        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials =! (empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        $is_not_authenticated = (
            !$has_supplied_credentials ||
            $_SERVER['PHP_AUTH_USER'] != $AUTH_USER ||
            $_SERVER['PHP_AUTH_PW']   != $AUTH_PASS
        );
        if ($is_not_authenticated) {
            header('HTTP/1.1 403 Signature Not Valid');
            header('WWW-Authenticate: Basic realm="Access denied"');     
            return response('Signature Not Valid',403);
        }
        return $next($request);
    }
}
