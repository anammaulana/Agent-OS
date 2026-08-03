<?php

namespace App\Exceptions;

use RuntimeException;

class DomainException extends RuntimeException
{
    public function __construct(string $message, private readonly int $status = 422, private readonly ?string $errorCode = null, private readonly array $errors = [])
    {
        parent::__construct($message);
    }
    public function status(): int
    {
        return $this->status;
    }
    public function errorCode(): ?string
    {
        return $this->errorCode;
    }
    public function errors(): array
    {
        return $this->errors;
    }
}
