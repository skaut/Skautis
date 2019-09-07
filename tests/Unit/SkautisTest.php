<?php

namespace Test\Skautis;

use PHPUnit_Framework_TestCase;
use Skautis\Config;
use Skautis\Skautis;

class SkautisTest extends PHPUnit_Framework_TestCase
{

    public function testSingletonSameId(): void
    {
        $skautis = Skautis::getInstance('asd');
        $skautisA = Skautis::getInstance('asd');
        $this->assertSame($skautis, $skautisA);
    }

    public function testSingletonDifferentId(): void
    {
        $skautis = Skautis::getInstance('asd');
        $skautisA = Skautis::getInstance('qwe');
        $this->assertNotSame($skautis, $skautisA);
    }

    public function testSingletonTestMode(): void
    {
        $appId = 'some-app-id';

        $skautisWithTestMode = Skautis::getInstance($appId, Config::TEST_MODE_ENABLED);
        $skautisWithoutTestMode = Skautis::getInstance($appId, Config::TEST_MODE_DISABLED);

        $this->assertNotSame($skautisWithTestMode, $skautisWithoutTestMode);
    }

    public function testEventSetter(): void
    {
        //@TODO
    }
}
