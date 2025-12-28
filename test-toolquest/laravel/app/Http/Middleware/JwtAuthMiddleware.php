<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuthMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('jwt');

        if (!$token) {
            return redirect()->route('login')->withErrors(['auth' => 'Sessie verlopen, log opnieuw in.']);
        }

        try {
            $secretKey = config('jwt.secret');
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            // Belangrijk: Zet de user data in het request object
            // Nu is het overal beschikbaar via $request->attributes
            $request->attributes->add(['authenticated_user' => $decoded]);

            return $next($request);
        } catch (\Exception $e) {
            return redirect()->route('login');
        }
    }
}
