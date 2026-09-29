<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl;

/**
 * A PHP error (typically a network failure) while checking whether skautIS is in maintenance.
 */
class MaintenanceErrorException extends WsdlException
{
    public function __construct(
        string $message,
        private readonly int $errno,
        private readonly string $errfile,
        private readonly int $errline,
    ) {
        parent::__construct($message);
    }

    public function getErrorNumber(): int
    {
        return $this->errno;
    }

    public function getErrorFile(): string
    {
        return $this->errfile;
    }

    public function getErrorLine(): int
    {
        return $this->errline;
    }
}
