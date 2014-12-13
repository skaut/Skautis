<?php

namespace Test\Skautis;

use Skautis\Config;

class ConfigTest
{

    public function testDefaultConfiguration()
    {
        $config = new Config("asd123");
	$this->assertTrue($config->validate());
	$this->assertEquals("asd123", $config->getAppId());
    }


    public function testTestMode()
    {
        $config = new Config("asd123");
	$config->setTestMode(Config::TESTMODE_ENABLED);

	$this->assertTrue($config->validate());
	$this->assertSame(Config::TESTMODE_ENABLED, $config->getTestMode());


	$config->setTestMode("asd");
	$this->assertFalse($config->validate());
    }

    public function testCompression()
    {
        $config = new Config("asd123");
	$config->setCompression(Config::COMPRESSION_ENABLED);

	$this->assertTrue($config->validate());
	$this->assertSame(Config::COMPRESSION_ENABLED, $config->getCompression());


	$config->setCompression("asd");
	$this->assertFalse($config->validate());
    }

    public function testProfiler()
    {
        $config = new Config("asd123");
	$config->setProfiler(Config::PROFILER_ENABLED);

	$this->assertTrue($config->validate());
	$this->assertSame(Config::PROFILER_ENABLED, $config->getProfiler());


	$config->setProfiler("asd");
	$this->assertFalse($config->validate());
    }

    public function testCache()
    {
        $config = new Config("asd123");
	$config->setCache(Config::CACHE_ENABLED);

	$this->assertTrue($config->validate());
	$this->assertSame(Config::CACHE_ENABLED, $config->getCache());


	$config->setCache("asd");
	$this->assertFalse($config->validate());
    }
}

