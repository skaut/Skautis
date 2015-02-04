<?php

namespace Test\Skautis;

use Skautis;
use Skautis\Wsdl\WebService;
use Skautis\Exception as SkautisException;


class WebServiceTest extends \PHPUnit_Framework_TestCase
{

    protected $queries = [];

    /**
     * @expectedException Skautis\InvalidArgumentException
     */
    public function testWebServiceConstructMissingWsdl()
    {
        $webService = new WebService("", []);
    }

    public function queryCallback($query)
    {
        $this->queries[] = $query;
    }

    public function testCallback()
    {
        $callback = [$this, 'queryCallback'];

        $data = [
            'ID_Application' => 123,
            Skautis\User::ID_LOGIN => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        ];
        $webService = new WebService("http://test-is.skaut.cz/JunakWebservice/UserManagement.asmx?WSDL", $data);
        $webService->subscribe(WebService::EVENT_FAILURE, $callback);

        try {
            $webService->call('UserDetail');
            $this->fail();
        } catch (SkautisException $e) {
        }

        $this->assertCount(1, $this->queries);
        $this->assertInstanceOf('Skautis\SkautisQuery', $this->queries[0]);
        $this->assertEquals('UserDetail', $this->queries[0]->fname);
        $this->assertGreaterThan(0, strlen($this->queries[0]->getExceptionString()));
        $this->assertEquals('SoapFault', $this->queries[0]->getExceptionClass());
        $this->assertTrue($this->queries[0]->hasFailed());
    }

    public function test__Call()
    {
        $callback = [$this, 'queryCallback'];

        $data = [
            'ID_Application' => 123,
            Skautis\User::ID_LOGIN => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        ];
        $webService = new WebService("http://test-is.skaut.cz/JunakWebservice/UserManagement.asmx?WSDL", $data);
        $webService->subscribe(WebService::EVENT_FAILURE, $callback);

        try {
            $webService->UserDetail();
            $this->fail();
        } catch (SkautisException $e) {
        }

        $this->assertCount(1, $this->queries);
        $this->assertInstanceOf('Skautis\SkautisQuery', $this->queries[0]);
        $this->assertEquals('UserDetail', $this->queries[0]->fname);
        $this->assertGreaterThan(0, strlen($this->queries[0]->getExceptionString()));
        $this->assertEquals('SoapFault', $this->queries[0]->getExceptionClass());
        $this->assertTrue($this->queries[0]->hasFailed());
    }
}
