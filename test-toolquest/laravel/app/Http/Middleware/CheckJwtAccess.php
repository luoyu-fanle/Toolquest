<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\JWTService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CheckJwtAccess
{
    protected $jwtService;
    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $jwtToken = $request->cookie('jwt'); 
        
        if (!$jwtToken) {
            // Geen token: direct weigeren
            return redirect('/login')->withErrors(['auth' => 'Toegang geweigerd: Geen JWT gevonden.']);
        }

        $path = $request->path();
        $userData = $this->jwtService->decodeRandomJwtToken($jwtToken);
        
        if (!is_array($userData)) {
            // Token ongeldig (verlopen of vervalst): weigeren
            return redirect('/login')->withErrors(['auth' => 'Toegang geweigerd: Ongeldige of verlopen token.']);
        }

        if (!isset($userData['payload']['roles']) || $userData['payload']['roles'] !== 'admin') {
            return redirect('/')->withErrors(['auth' => 'Toegang geweigerd: Onvoldoende rechten.']);
        }

        return $next($request);
    }
}
