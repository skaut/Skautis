<?php
declare(strict_types = 1);

namespace Skaut\Skautis\Wsdl;

use Skaut\Skautis\Exception as SkautisException;

interface WebServiceInterface
{

    /**
     * Zavola funkci na Skautisu
     *
     * @param string $functionName Jmeno funkce volane na skautisu
     * @param array<string, mixed> $arguments    Argumenty funkce volane na skautisu
     *
     * @throws SkautisException
     *
     * @return mixed
     */
    public function call(string $functionName, array $arguments = []);

    /**
     * Zavola funkci na Skautisu
     *
     * @param string $functionName Jmeno funkce volane na skautisu
     * @param array<string, mixed> $arguments    Argumenty funkce volane na skautisu
     *
     * @throws SkautisException
     *
     * @return mixed
     */
    public function __call(string $functionName, array $arguments);
}
