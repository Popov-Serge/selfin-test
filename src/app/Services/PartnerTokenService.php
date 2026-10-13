<?php

namespace App\Services;

use App\Models\Partner;
use Illuminate\Support\Str;

class PartnerTokenService
{
    /**
     * Выпускает новый токен партнёра.
     * Исходный токен возвращается только один раз.
     */
    public function issue(Partner $partner): string
    {
        $tokenId = Str::random(16);
        $secret = Str::random(64);

        $partner->api_token_id = $tokenId;
        $partner->api_token_hash = hash('sha256', $secret);
        $partner->save();

        return "pt_{$tokenId}.{$secret}";
    }

    public function authenticate(string $token): ?Partner
    {
        if (! preg_match(
            '/^pt_([A-Za-z0-9]{16})\.([A-Za-z0-9]{64})$/',
            $token,
            $matches,
        )) {
            return null;
        }

        $partner = Partner::query()
            ->where('api_token_id', $matches[1])
            ->where('is_active', true)
            ->first();

        if ($partner === null || $partner->api_token_hash === null) {
            return null;
        }

        $secretHash = hash('sha256', $matches[2]);

        if (! hash_equals($partner->api_token_hash, $secretHash)) {
            return null;
        }

        return $partner;
    }
}
