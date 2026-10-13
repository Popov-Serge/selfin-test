<?php

namespace Tests\Unit;

use App\Http\Middleware\AuthenticatePartner;
use App\Models\Partner;
use App\Services\PartnerTokenService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthenticatePartnerTest extends TestCase
{
    public function test_request_without_bearer_token_returns401(): void
    {
        $tokenService = $this->createMock(PartnerTokenService::class);
        $tokenService
            ->expects($this->never())
            ->method('authenticate');

        $middleware = new AuthenticatePartner($tokenService);
        $request = Request::create('/api/webhook/status', 'POST');

        $nextCalled = false;

        $response = $middleware->handle(
            $request,
            function () use (&$nextCalled) {
                $nextCalled = true;

                return response()->json(['status' => 'ok']);
            },
        );

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertFalse($nextCalled);
    }

    public function test_invalid_token_returns401(): void
    {
        $tokenService = $this->createMock(PartnerTokenService::class);
        $tokenService
            ->expects($this->once())
            ->method('authenticate')
            ->with('invalid-token')
            ->willReturn(null);

        $middleware = new AuthenticatePartner($tokenService);
        $request = Request::create('/api/webhook/status', 'POST', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token',
        ]);

        $nextCalled = false;

        $response = $middleware->handle(
            $request,
            function () use (&$nextCalled) {
                $nextCalled = true;

                return response()->json(['status' => 'ok']);
            },
        );

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertFalse($nextCalled);
    }

    public function test_malformed_authorization_header_returns401(): void
    {
        $tokenService = $this->createMock(PartnerTokenService::class);
        $tokenService
            ->expects($this->never())
            ->method('authenticate');

        $middleware = new AuthenticatePartner($tokenService);
        $request = Request::create('/api/webhook/status', 'POST', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Basic some-token',
        ]);

        $nextCalled = false;

        $response = $middleware->handle(
            $request,
            function () use (&$nextCalled) {
                $nextCalled = true;

                return response()->json(['status' => 'ok']);
            },
        );

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertFalse($nextCalled);
    }

    public function test_valid_token_adds_partner_to_request_attributes(): void
    {
        $partner = new Partner;
        $partner->id = 42;

        $tokenService = $this->createMock(PartnerTokenService::class);
        $tokenService
            ->expects($this->once())
            ->method('authenticate')
            ->with('valid-token')
            ->willReturn($partner);

        $middleware = new AuthenticatePartner($tokenService);
        $request = Request::create('/api/webhook/status', 'POST', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer valid-token',
        ]);

        $response = $middleware->handle(
            $request,
            function (Request $request) use ($partner) {
                $this->assertSame(
                    $partner,
                    $request->attributes->get('authenticated_partner'),
                );

                return response()->json(['status' => 'ok']);
            },
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
