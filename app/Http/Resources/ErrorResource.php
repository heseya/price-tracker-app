<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ErrorResource extends JsonResource
{
    /**
     * @var string
     */
    public static $wrap = 'error';

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'errors' => $this->errors,
        ];
    }
}
