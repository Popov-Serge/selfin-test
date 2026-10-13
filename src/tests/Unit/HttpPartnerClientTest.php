<?php

namespace Tests\Unit;

use App\Exceptions\PartnerRequestException;
use App\Models\Lead;
use App\Models\Partner;
use App\Services\HttpPartnerClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HttpPartnerClientTest extends TestCase
{
    private function makeLead(): Lead
    {
        return new Lead([
            'first_name' => 'Ivan',
            'last_name' => 'Ivanov',
            'phone' => '+79990000000',
            'email' => 'ivan@example.com',
            'amount' => 15000000,
            'term' => 12,
        ]);
    }

    private function makePartner(): Partner
    {
        return new Partner([
            'name' => 'Test Partner',
            'slug' => 'test-partner',
            'endpoint_url' => 'https://partner.example.test/leads',
            'is_active' => true,
            'timeout_seconds' => 5,
        ]);
    }

    public function test_it_returns_request_id_on_success(): void
    {
        Http::fake([
            '*' => Http::response([
                'request_id' => 'a1b2c3d4',
            ], 200),
        ]);

        $requestId = app(HttpPartnerClient::class)->send(
            $this->makeLead(),
            $this->makePartner(),
        );

        $this->assertSame('a1b2c3d4', $requestId);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'POST'
                && $request->url() === 'https://partner.example.test/leads'
                && $request->hasHeader('Accept', 'application/json')
                && $request['first_name'] === 'Ivan'
                && $request['last_name'] === 'Ivanov'
                && $request['phone'] === '+79990000000'
                && $request['email'] === 'ivan@example.com'
                && $request['amount'] === 15000000
                && $request['term'] === 12;
        });
    }

    public function test_it_throws_exception_for_http_error(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Unavailable'], 503),
        ]);

        try {
            app(HttpPartnerClient::class)->send(
                $this->makeLead(),
                $this->makePartner(),
            );

            $this->fail('Expected PartnerRequestException was not thrown.');
        } catch (PartnerRequestException $exception) {
            $this->assertSame(503, $exception->httpStatus);
        }
    }

    public function test_it_throws_exception_when_request_id_is_missing(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'accepted'], 200),
        ]);

        $this->expectException(PartnerRequestException::class);

        app(HttpPartnerClient::class)->send(
            $this->makeLead(),
            $this->makePartner(),
        );
    }

    public function test_it_throws_exception_when_request_id_is_empty(): void
    {
        Http::fake([
            '*' => Http::response(['request_id' => '  '], 200),
        ]);

        $this->expectException(PartnerRequestException::class);

        app(HttpPartnerClient::class)->send(
            $this->makeLead(),
            $this->makePartner(),
        );
    }

    public function test_it_propagates_connection_exception(): void
    {
        Http::fake([
            '*' => function () {
                throw new ConnectionException('Connection timed out');
            },
        ]);

        $this->expectException(ConnectionException::class);

        app(HttpPartnerClient::class)->send(
            $this->makeLead(),
            $this->makePartner(),
        );
    }
}
