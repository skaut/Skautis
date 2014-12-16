<?php

namespace Test\Skautis;

use Skautis\WsdlManager;

class WsdlManagerTest
{

    protected function makeFactory()
    {
        return \Mockery::mock("\Skautis\Factory\WSFactory");
    }

    public function testGetWsdlList() 
    {

        $skautIS = new WsdlManager();
        $wdlList = $skautIS->getWsdlList();

        $this->assertInternalType('array', $wdlList);
        $this->assertTrue(count($wdlList) > 0);

        foreach ($wdlList as $key => $value) {
            $this->assertSame($key, $value);
        }
    }


    public function testGetWsdl() 
    {
        $wsA = new \StdClass;
        $wsB = new \StdClass;

        $factory = \Mockery::mock("\Skautis\Factory\WSFactory");
        $factory->shouldReceive("createWS")->with()->twice()->andReturn($wsA, $wsB);

        $skautIS = new Skautis("123");
        $skautIS->setWSFactory($factory);


        $skautIS->setTestMode(true);
        $eventA = $skautIS->event;
        $this->assertSame($eventA, $skautIS->event);

        $skautIS->setTestMode(false);
        $this->assertNotSame($eventA, $skautIS->event);
        $eventB = $skautIS->event;

        $skautIS->setTestMode(true);
        $this->assertSame($eventA, $skautIS->event);

        $this->assertSame($wsA, $eventA);
        $this->assertSame($wsB, $eventB);
    }
}

