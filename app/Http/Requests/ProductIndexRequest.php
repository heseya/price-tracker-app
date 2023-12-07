<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_ids' => ['required', 'array'],
            'currency' => ['string'],
        ];
    }
}