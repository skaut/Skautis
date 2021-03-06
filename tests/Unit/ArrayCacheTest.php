<?php

namespace Test\Skautis;

use Skautis\Wsdl\Decorator\Cache\ArrayCache;

class ArrayCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testCache()
    {
        //@TODO $cache jako parametr a umoznit opakovane pouziti testu
        $cache = new ArrayCache();

        $key = 'asdasdqwer25erg';
        $value = ['text' => 'Lorem ..', 'date' => new \DateTime()];

        $cache->set($key, $value);
        $this->assertEquals($value, $cache->get($key));
    }
}
