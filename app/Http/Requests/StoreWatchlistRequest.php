<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWatchlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => ['required', 'integer', 'exists:countries,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'country_id.required' => 'Negara wajib dipilih.',
            'country_id.exists'   => 'Negara tidak ditemukan.',
        ];
    }
}