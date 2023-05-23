<?php

namespace App\Http\Middleware;

use App\Services\SalesforceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySalesforceSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->exists(SalesforceService::$sfdcUserSessionKey)) {
            return response()->json([
                'message' => 'Invalid session. You are not authorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
