<?php

declare(strict_types=1);

namespace App\Exceptions;

final class Error
{
    /**
     * Http response code.
     */
    public int $code;

    /**
     * Error message.
     */
    public string $message;

    /**
     * Errors details.
     */
    public array $errors;

    public function __construct(string $message = 'Internal Server Error', int $code = 500, array $errors = [])
    {
        $this->message = $message;
        $this->code = $code;
        $this->errors = $errors;
    }
}
