<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionClassConstant;
use Skaut\Skautis\Wsdl\WebServiceName;

final class WebServiceNameTest extends TestCase
{
    public function testValidNames(): void
    {
        self::assertTrue(WebServiceName::isValidServiceName(WebServiceName::APPLICATION_MANAGEMENT));
        self::assertTrue(WebServiceName::isValidServiceName(WebServiceName::GRANTS));
        self::assertTrue(WebServiceName::isValidServiceName(WebServiceName::WELCOME));
    }

    public function testAliasesAreNotNames(): void
    {
        self::assertFalse(WebServiceName::isValidServiceName('usr'));
        self::assertFalse(WebServiceName::isValidServiceName('user'));
        self::assertFalse(WebServiceName::isValidServiceName('usermanagement'));
    }

    public function testGetAllContainsEveryConstant(): void
    {
        $constants = (new ReflectionClass(WebServiceName::class))->getConstants(ReflectionClassConstant::IS_PUBLIC);

        self::assertSame(array_values($constants), WebServiceName::getAll());
    }
}
