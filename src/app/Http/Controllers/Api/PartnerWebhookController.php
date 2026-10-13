<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StorePartnerWebhookRequest;
use App\Services\PartnerWebhookService;
use Illuminate\Http\JsonResponse;

final class PartnerWebhookController
{
    public function __construct(
        private readonly PartnerWebhookService $webhookService,
    ) {}

    public function handle(StorePartnerWebhookRequest $request): JsonResponse
    {
        $partner = $request->attributes->get('authenticated_partner');

        if ($partner === null) {
            abort(401, 'Unauthenticated.');
        }

        $event = $this->webhookService->handle(
            $partner->id,
            $request->toDto(),
        );

        return response()->json([
            'status' => 'ok',
            'event_id' => $event->id,
        ]);
    }
}
