<?php

namespace Test\SkautIS;

use SkautIS\SessionAdapter\AdapterInterface;
use SkautIS\SessionAdapter\NetteAdapter;
use SkautIS\SessionAdapter\SymfonyAdapter;
use SkautIS\SessionAdapter\SessionAdapter;

use Nette\Http\UrlScript;
use Nette\Http\Response;
use Nette\Http\Request;
use Nette\Http\Session;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SessionAdaptersTest extends \PHPUnit_Framework_TestCase
{

   /**
    * @runInSeparateProcess
    */
   public function testAdapter()
   {

	$adapter = new SessionAdapter();

	$name = "asd";
	$data = new \StdClass();

	$data->data['user_id'] = 123;
	$data->data['token'] = 'asdqwe';



	$this->assertFalse($adapter->has($name));

	$adapter->set($name, $data);

	$this->assertTrue($adapter->has($name));
	$this->assertEquals($data, $adapter->get($name));


	$object = $adapter->get($name);
	$this->assertEquals(123, $object->data['user_id']);
	$this->assertEquals("asdqwe", $object->data['token']);
  }
}
