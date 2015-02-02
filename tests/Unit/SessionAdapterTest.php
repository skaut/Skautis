<?php

namespace Test\Skautis;

use Skautis\SessionAdapter\AdapterInterface;
use Skautis\SessionAdapter\SessionAdapter;
use Skautis\SessionAdapter\FakeAdapter;

class SessionAdaptersTest extends \PHPUnit_Framework_TestCase {


    public function getAdapters()
    {
        return array(
	    array(new FakeAdapter()),
            array(new SessionAdapter()),
	);
    }

    /**
     * @dataProvider getAdapters
     * @runInSeparateProcess
     */
    public function testAdapterMethods(AdapterInterface $adapter)
    {

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


    /**
     * @runInSeparateProcess
     */
    public function testSessionAdapter()
    {
	session_start();
	session_unset();

	$adapter = new SessionAdapter();

	$nameA = "promena by byla lepsi \$key";
	$dataA = "somesuper data";
	$nameB = "Klic sice je lepsi ale co se delat";
	$dataB = "Ze sis radsi nenainstaloval Faker";
        $this->assertFalse($adapter->has($nameA));

        $adapter->set($nameA, $dataA);
        $adapter->set($nameB, $dataB);

        $this->assertTrue($adapter->has($nameA));
	$this->assertEquals($dataA, $adapter->get($nameA));
	$this->assertEquals($dataB, $adapter->get($nameB));
	$this->assertCount(1, $_SESSION);

	$values = array_values($_SESSION);
	$this->assertCount(2, $values[0]);
	$this->assertContains($dataA, $values[0]);
	$this->assertContains($dataB, $values[0]);

	$data = session_encode();
        session_unset();
	$this->assertCount(0, (array)$_SESSION);

	session_decode($data);
	$this->assertCount(1, $_SESSION);

	$values = array_values($_SESSION);
	$this->assertCount(2, $values[0]);

	$adapterNew = new SessionAdapter();

        $this->assertTrue($adapterNew->has($nameA));
        $this->assertTrue($adapterNew->has($nameB));
        $this->assertEquals($dataA, $adapterNew->get($nameA));
        $this->assertEquals($dataB, $adapterNew->get($nameB));
    }
}
