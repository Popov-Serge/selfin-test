<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLeadRequest;
use App\Jobs\DeliverLeadToPartner;
use App\Models\Lead;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class LeadController
{
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $data = $request->validated();

        $lead = DB::transaction(function () use ($data): Lead {
            return Lead::query()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'patronymic' => $data['patronymic'] ?? null,
                'birth_date' => $data['birth_date'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'amount' => $data['amount'] * 100,
                'term' => $data['term'],
                'issuing_region' => $data['issuing_region'] ?? null,
                'issuing_region_kladr_id' => $data['issuing_region_kladr_id'] ?? null,
                'issuing_city' => $data['issuing_city'] ?? null,
                'employment_type' => $data['employmentType'] ?? null,
                'income' => $data['income'] ?? null,
                'education_type' => $data['education_type'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'payload' => [
                    'passport' => $data['passport'] ?? null,
                    'address_registration' => $data['address_registration'] ?? null,
                    'address_residence' => $data['address_residence'] ?? null,
                    'sub_ids' => $data['sub_ids'] ?? null,
                ],
            ]);
        });

        $partners = Partner::query()
            ->where('is_active', true)
            ->get(['id']);

        foreach ($partners as $partner) {
            DeliverLeadToPartner::dispatch($lead->id, $partner->id)
                ->onQueue('default')
                ->afterCommit();
        }

        return response()->json([
            'message' => 'Lead accepted',
            'lead_id' => $lead->id,
            'deliveries_queued' => $partners->count(),
        ], 202);
    }
}
