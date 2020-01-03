<?php

namespace Test\Skautis;

use PHPUnit\Framework\TestCase;
use Skautis\Helpers;
use Skautis\User;

class HelpersTest extends TestCase
{

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testSetLoginData()
    {
        $data = [
            'skautIS_Token' => "token",
            'skautIS_IDRole' => 33,
            'skautIS_IDUnit' => 100,
            'skautIS_DateLogout' => '2. 12. 2014 23:56:02'
        ];

        $parsed = Helpers::parseLoginData($data);

        $this->assertEquals("token", $parsed[User::ID_LOGIN]);
        $this->assertEquals(33, $parsed[User::ID_ROLE]);
        $this->assertEquals(100, $parsed[User::ID_UNIT]);
        $this->assertEquals("2014-12-02 23:56:02", $parsed[User::LOGOUT_DATE]->format('Y-m-d H:i:s'));
    }
}
