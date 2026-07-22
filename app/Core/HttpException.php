<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class HttpException extends RuntimeException
{
    public function __construct(
        private int $statusCode,
        private string $pageTitle,
        string $message = ''
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getPageTitle(): string
    {
        return $this->pageTitle;
    }
}
