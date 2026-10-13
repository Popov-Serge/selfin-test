<?php

namespace App\Http\Middleware;

use App\Services\PartnerTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePartner
{
    public function __construct(
        private readonly PartnerTokenService $tokenService,
    ) {}

    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $authorization = $request->header('Authorization', '');

        if (! preg_match(
            '/^Bearer\s+(\S+)$/i',
            $authorization,
            $matches,
        )) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $partner = $this->tokenService->authenticate($matches[1]);

        if ($partner === null) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->attributes->set('authenticated_partner', $partner);

        return $next($request);
    }
}
