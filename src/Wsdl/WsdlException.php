<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl;

use Exception;
use Skaut\Skautis;

/**
 * Obecná chyba při komunikaci s webovými službami.
 *
 * @author Hána František <sinacek@gmail.com>
 */
class WsdlException extends Exception implements Skautis\Exception
{
}
