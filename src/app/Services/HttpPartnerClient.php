<?php

namespace App\Services;

use App\Exceptions\PartnerRequestException;
use App\Models\Lead;
use App\Models\Partner;
use Illuminate\Support\Facades\Http;

class HttpPartnerClient implements PartnerClientInterface
{
    /**
     * {@inheritDoc}
     */
    public function send(Lead $lead, Partner $partner): string
    {
        $response = Http::timeout($partner->timeout_seconds)
            ->acceptJson()
            ->post($partner->endpoint_url, [
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'amount' => $lead->amount,
                'term' => $lead->term,
            ]);

        if ($response->failed()) {
            throw new PartnerRequestException(
                message: sprintf(
                    'Partner returned HTTP %d',
                    $response->status(),
                ),
                httpStatus: $response->status(),
            );
        }

        $requestId = $response->json('request_id');

        if (! is_string($requestId) || trim($requestId) === '') {
            throw new PartnerRequestException(
                message: sprintf(
                    'Partner returned HTTP %d',
                    $response->status(),
                ),
                httpStatus: $response->status(),
            );
        }

        return $requestId;
    }
}
