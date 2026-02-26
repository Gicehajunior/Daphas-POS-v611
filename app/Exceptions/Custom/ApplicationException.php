<?php

namespace App\Exceptions\Custom;

use Exception;

class ApplicationException extends Exception
{
    protected int $statusCode;

    public function __construct(
        string $message,
        int $statusCode = 400
    ) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
