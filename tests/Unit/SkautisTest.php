<?php

namespace Test\Skautis;

use Skautis\Skautis;

class SkautisTest extends \PHPUnit_Framework_TestCase
{

    public function testFactorySingletonHybrid()
    {
        $skautis = Skautis::getInstance('789qwe');
        $this->assertInstanceOf('\Skautis\Skautis', $skautis);

        $skautisA = Skautis::getInstance('789qwe');
        $this->assertSame($skautis, $skautisA);

        $skautisB = Skautis::getInstance('nejake_jine_appid');
        $this->assertNotSame($skautis, $skautisB);
    }

    public function testEventSetter()
    {
        //@TODO
    }
}
