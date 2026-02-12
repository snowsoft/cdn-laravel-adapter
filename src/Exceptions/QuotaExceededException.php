<?php

namespace CdnServices\Exceptions;

use Exception;

/**
 * Depolama kotası aşıldı (API 413).
 * Backend'de USER_STORAGE_QUOTA_BYTES tanımlı ve kullanıcı limiti aştığında fırlatılır.
 */
class QuotaExceededException extends Exception
{
    protected int $statusCode = 413;

    public function __construct(string $message = 'Depolama kotası aşıldı', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
