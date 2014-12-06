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
	$this->assertEquals("getUser", $query->fname);


	$this->assertSame("", $query->getExceptionClass());
	$this->assertFalse($query->hasFailed());
    }

    public function testQueryException()
    {
	$query = new SkautisQuery("getUser");
	sleep(1);

	$exception = new \Exception("Testovaci vyjimka");
	$query->done(NULL, $exception);


	$this->assertTrue($query->time >= 1);
	$this->assertTrue($query->time < 2);
	$this->assertEquals("getUser", $query->fname);
	$this->assertSame(get_class($exception), $query->getExceptionClass());
	$this->assertTrue($query->hasFailed());
    }

    public function testQuerySerialization()
    {
	$query = new SkautisQuery("getUser");
	$query->done();

	$serialized = serialize($query);
	unset($query);

	$query = unserialize($serialized);
	$this->assertEquals("getUser", $query->fname);
	$this->assertFalse($query->hasFailed());
    }

    public function testQuerySerializationWithSoapFault()
    {
	$query = new SkautisQuery("getUser");
	$exception = new \SoapFault("test", "msg");
	$query->done(NULL, $exception);

	$serialized = serialize($query);
	unset($query);

	$query = unserialize($serialized);
	$this->assertEquals("getUser", $query->fname);
	$this->assertSame(get_class($exception), $query->getExceptionClass());
	$this->assertTrue($query->hasFailed());
    }
}
