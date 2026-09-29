<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl;

use Skaut\Skautis\Exception as SkautisException;

interface WebServiceInterface
{
    /**
     * Calls a skautIS method.
     *
     * @param string                   $functionName name of the skautIS method, e.g. UnitDetail
     * @param array<int|string, mixed> $arguments    [0] => method arguments, optional [1] => custom input wrapper name
     *
     * @throws SkautisException
     */
    public function call(string $functionName, array $arguments = []): mixed;

    /**
     * Same as call(): $service->UnitDetail(['ID' => 1]).
     *
     * @param array<int|string, mixed> $arguments
     *
     * @throws SkautisException
     */
    public function __call(string $functionName, array $arguments): mixed;
}
