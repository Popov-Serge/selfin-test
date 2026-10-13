<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'patronymic' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['required', 'date_format:Y-m-d'],

            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],

            // В примере сумма передаётся в рублях.
            'amount' => ['required', 'integer', 'min:1'],
            'term' => ['required', 'integer', 'min:1', 'max:65535'],

            'issuing_region' => ['nullable', 'string', 'max:255'],
            'issuing_region_kladr_id' => ['nullable', 'string', 'max:32'],
            'issuing_city' => ['nullable', 'string', 'max:255'],

            'employmentType' => ['nullable', 'string', 'max:255'],
            'income' => ['nullable', 'string', 'max:255'],
            'education_type' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],

            'passport' => ['required', 'array'],
            'passport.series' => ['required', 'string', 'max:20'],
            'passport.number' => ['required', 'string', 'max:20'],
            'passport.issued_by' => ['required', 'string', 'max:255'],
            'passport.issued_date' => ['required', 'date_format:Y-m-d'],
            'passport.division_code' => ['required', 'string', 'max:20'],

            'address_registration' => ['required', 'array'],
            'address_registration.region' => ['required', 'string', 'max:255'],
            'address_registration.region_kladr_id' => ['nullable', 'string', 'max:32'],
            'address_registration.city' => ['required', 'string', 'max:255'],
            'address_registration.street' => ['required', 'string', 'max:255'],
            'address_registration.house' => ['required', 'string', 'max:50'],
            'address_registration.housing' => ['nullable', 'string', 'max:50'],
            'address_registration.flat' => ['nullable', 'string', 'max:50'],

            'address_residence' => ['required', 'array'],
            'address_residence.region' => ['required', 'string', 'max:255'],
            'address_residence.region_kladr_id' => ['nullable', 'string', 'max:32'],
            'address_residence.city' => ['required', 'string', 'max:255'],
            'address_residence.street' => ['required', 'string', 'max:255'],
            'address_residence.house' => ['required', 'string', 'max:50'],
            'address_residence.housing' => ['nullable', 'string', 'max:50'],
            'address_residence.flat' => ['nullable', 'string', 'max:50'],

            'sub_ids' => ['required', 'array'],
            'sub_ids.sub_id_1' => ['nullable', 'string', 'max:255'],
            'sub_ids.sub_id_2' => ['nullable', 'string', 'max:255'],
            'sub_ids.sub_id_3' => ['nullable', 'string', 'max:255'],
            'sub_ids.sub_id_4' => ['nullable', 'string', 'max:255'],
            'sub_ids.sub_id_5' => ['nullable'],
            'sub_ids.sub_id_6' => ['nullable', 'string', 'max:255'],
            'sub_ids.sub_id_7' => ['nullable', 'string', 'max:255'],
            'sub_ids.sub_id_8' => ['nullable', 'string', 'max:255'],
        ];
    }
}
