<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit;

use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Helpers;
use Skaut\Skautis\UnexpectedValueException;
use Skaut\Skautis\User;

final class HelpersTest extends TestCase
{
    public function testParseLoginData(): void
    {
        $parsed = Helpers::parseLoginData([
            'skautIS_Token' => 'token',
            'skautIS_IDRole' => '33',
            'skautIS_IDUnit' => 100,
            'skautIS_DateLogout' => '2. 12. 2014 23:56:02',
        ]);

        self::assertSame('token', $parsed[User::ID_LOGIN]);
        self::assertSame(33, $parsed[User::ID_ROLE]);
        self::assertSame(100, $parsed[User::ID_UNIT]);
        self::assertNotNull($parsed[User::LOGOUT_DATE]);
        self::assertSame('2014-12-02 23:56:02', $parsed[User::LOGOUT_DATE]->format('Y-m-d H:i:s'));
    }

    public function testMissingFieldsAreNull(): void
    {
        $parsed = Helpers::parseLoginData([]);

        self::assertNull($parsed[User::ID_LOGIN]);
        self::assertNull($parsed[User::ID_ROLE]);
        self::assertNull($parsed[User::ID_UNIT]);
        self::assertNull($parsed[User::LOGOUT_DATE]);
    }

    public function testInvalidLogoutDateIsRejected(): void
    {
        $this->expectException(UnexpectedValueException::class);
        Helpers::parseLoginData(['skautIS_DateLogout' => 'not a date']);
    }
}
