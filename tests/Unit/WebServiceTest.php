<?php

namespace Skaut\Skautis\Test\Unit;

use PHPUnit\Framework\TestCase;
use Skautis;
use Skautis\Exception as SkautisException;
use Skautis\SkautisQuery;
use Skautis\Wsdl\WebService;
use Skautis\Wsdl\WebServiceFactory;

class WebServiceTest extends TestCase
{

    protected $queries = [];

    /**
     * @var WebServiceFactory
     */
    private $wsFactory;

    protected function setUp(): void
    {
      $this->wsFactory = new WebServiceFactory();
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


        $webService = $this->wsFactory->createWebService(
          'https://test-is.skaut.cz/JunakWebservice/UserManagement.asmx?WSDL',
          $data
        );
        $webService->subscribe(WebService::EVENT_FAILURE, $callback);

        try {
            $webService->call('UserDetail');
            $this->fail();
        } catch (SkautisException $e) {
            //Vyjimku chceme
        }

        $this->assertCount(1, $this->queries);
        $this->assertInstanceOf(SkautisQuery::class, $this->queries[0]);
        $this->assertEquals('UserDetail', $this->queries[0]->fname);
        $this->assertGreaterThan(0, strlen($this->queries[0]->getExceptionString()));
        $this->assertEquals('SoapFault', $this->queries[0]->getExceptionClass());
        $this->assertTrue($this->queries[0]->hasFailed());
    }

    public function testCall()
    {
        $callback = [$this, 'queryCallback'];

        $data = [
            'ID_Application' => 123,
            Skautis\User::ID_LOGIN => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        ];
        $webService = $this->wsFactory->createWebService(
          'https://test-is.skaut.cz/JunakWebservice/UserManagement.asmx?WSDL',
          $data
        );
        $webService->subscribe(WebService::EVENT_FAILURE, $callback);

        try {
            $webService->UserDetail();
            $this->fail();
        } catch (SkautisException $e) {
            //Vyjimku chceme
        }

        $this->assertCount(1, $this->queries);
        $this->assertInstanceOf(SkautisQuery::class, $this->queries[0]);
        $this->assertEquals('UserDetail', $this->queries[0]->fname);
        $this->assertGreaterThan(0, strlen($this->queries[0]->getExceptionString()));
        $this->assertEquals('SoapFault', $this->queries[0]->getExceptionClass());
        $this->assertTrue($this->queries[0]->hasFailed());
    }
}
