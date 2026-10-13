<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadDelivery;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;

class LeadDeliveryService
{
    public function __construct(
        private readonly PartnerClientInterface $partnerClient
    ) {}

    public function deliver(Lead $lead, Partner $partner): LeadDelivery
    {
        $delivery = LeadDelivery::query()->firstOrCreate(
            [
                'lead_id' => $lead->id,
                'partner_id' => $partner->id,
            ],
            [
                'status' => 'pending',
                'attempts_count' => 0,
            ],
        );

        if ($delivery->status === 'sent') {
            return $delivery;
        }

        $delivery->increment('attempts_count');

        try {
            $requestId = $this->partnerClient->send($lead, $partner);

            return DB::transaction(function () use ($delivery, $requestId, $lead, $partner) {
                $delivery->request_id = $requestId;
                $delivery->status = 'sent';
                $delivery->last_error = null;
                $delivery->sent_at = now();
                $delivery->save();

                logger()->info('Lead delivery succeeded', [
                    'lead_id' => $lead->id,
                    'partner_id' => $partner->id,
                    'delivery_id' => $delivery->id,
                ]);

                return $delivery->refresh();
            });
        } catch (\Throwable $exception) {
            $delivery->status = 'failed';
            $delivery->last_error = $exception->getMessage();
            $delivery->save();

            logger()->error('Lead delivery failed', [
                'lead_id' => $lead->id,
                'partner_id' => $partner->id,
                'delivery_id' => $delivery->id,
                'attempt' => $delivery->attempts_count,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
