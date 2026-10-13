<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Services\PartnerTokenService;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partner = Partner::query()->firstOrCreate(
            ['slug' => 'demo-partner'],
            [
                'name' => 'Demo Partner',
                'endpoint_url' => 'https://example.com/webhook',
                'is_active' => true,
                'timeout_seconds' => 10,
            ],
        );

        if ($partner->api_token_id !== null && $partner->api_token_hash !== null) {
            $this->command?->info('Demo partner already has an API token.');

            return;
        }

        $token = app(PartnerTokenService::class)->issue($partner);

        $this->command?->info('Demo partner API token (save it now):');
        $this->command?->line($token);
    }
}
