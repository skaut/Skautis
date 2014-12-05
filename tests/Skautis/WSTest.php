<?php

namespace Test\Skautis;

use Skautis\WS;
use Skautis\Skautis;

class WSTest extends \PHPUnit_Framework_TestCase
{
    protected $queries = array();

    /**
     * @expectedException SkautIS\Exception\AbortException
     */
    public function testWSConstructMissingWsdl()
    {
        $ws = new WS("", array());
    }

    public function queryCallback($query)
    {
	$this->queries[] = $query;
    }

    public function testCallback()
    {
        $callback = array($this, 'queryCallback');


	$data = array(
            Skautis::APP_ID => 123,
            Skautis::TOKEN => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
	);
	$ws = new WS("http://test-is.skaut.cz/JunakWebservice/UserManagement.asmx?WSDL", $data);
	$ws->profiler = true;
	$ws->addCallback($callback);

	try {
            $ws->UserDetail();
	}
	catch (\Exception $e) {}

	$this->assertCount(1,$this->queries);
	$this->assertInstanceOf('Skautis\SkautisQuery', $this->queries[0]);
	$this->assertEquals('UserDetail', $this->queries[0]->fname);
	$this->assertNotNull($this->queries[0]->exception);
	$this->assertInstanceOf('SoapFault', $this->queries[0]->exception);
	$this->assertTrue($this->queries[0]->hasFailed());
    }
}
