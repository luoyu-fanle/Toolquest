<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Service\JWTService;

class JwtAuthMiddleware
{
    protected $jwtService;
    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('jwt');
        if (!$token) {
            return redirect()->route('login')->withErrors(['auth' => 'Authentication required. Please log in.']);
        }

        $decodedJWT = $this->jwtService->decodeRandomJwtToken($token);
        if (!is_array($decodedJWT)) {
            return redirect()->route('login')->withErrors(['auth' => 'Authentication required. Please log in.']);
        }
        $request->attributes->add(['authenticated_user' => $decodedJWT['payload']]);
        return $next($request);
    }
}
