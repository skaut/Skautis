<?php
declare(strict_types = 1);

namespace Skautis;

use LogicException;

/**
 * Vyhozena v případě pokusu o vytvoření instance statické třídy.
 *
 * @author Petr Morávek <petr@pada.cz>
 */
class StaticClassException extends LogicException implements Exception
{
}
