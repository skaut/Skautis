<?php

declare(strict_types=1);

namespace Skaut\Skautis;

use RuntimeException;

class DynamicPropertiesDisabledException extends RuntimeException implements Exception
{
    public function __construct(string $message = 'This class does not support dynamic properties')
    {
        parent::__construct($message);
    }
}
