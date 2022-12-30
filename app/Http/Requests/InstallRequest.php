<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstallRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'api_url' => ['required', 'string', Rule::unique('apis', 'url')],
            'integration_token' => ['required', 'string'],
            'refresh_token' => ['required', 'string'],
            'api_version' => ['required', 'string'],
        ];
    }
}
