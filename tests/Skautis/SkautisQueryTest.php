<?php

namespace Test\Skautis;

use Skautis\SkautisQuery;

class SkautisQueryTest extends \PHPUnit_Framework_TestCase
{
    public function testQuery()
    {
	$query = new SkautisQuery("getUser");
	sleep(1);
	$query->done();

	$this->assertTrue($query->time >= 1);
	$this->assertTrue($query->time < 2);
    }
}
