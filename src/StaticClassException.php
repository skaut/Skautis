<?php

namespace Skautis;

/**
 * Vyhozena v případě pokusu o vytvoření instance statické třídy.
 *
 * @author Petr Morávek <petr@pada.cz>
 */
class StaticClassException extends \LogicException implements Exception
{
}
