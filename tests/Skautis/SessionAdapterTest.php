<?php

namespace Test\Skautis;

use Skautis\SessionAdapter\SessionAdapter;

class SessionAdaptersTest extends \PHPUnit_Framework_TestCase {

    /**
     * @runInSeparateProcess
     */
    public function testAdapter() {

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
