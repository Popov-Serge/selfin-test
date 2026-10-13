<?php

namespace App\Http\Requests;

use App\DTO\PartnerWebhookData;
use Illuminate\Foundation\Http\FormRequest;

class StorePartnerWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_id' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:approved,rejected'],
            'amount' => ['required', 'string', 'regex:/^\d+(?:\.\d{1,2})?$/'],
        ];
    }

    public function toDto(): PartnerWebhookData
    {
        $validated = $this->validated();

        return new PartnerWebhookData(
            requestId: $validated['request_id'],
            status: $validated['status'],
            amount: (string) $validated['amount'],
        );
    }
}
