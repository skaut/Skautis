<?php

namespace Test\Skautis;

use Skautis\Config;


class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultConfiguration()
    {
        $config = new Config("asd123");

        $this->assertEquals("asd123", $config->getAppId());
        $this->assertSame(Config::TESTMODE_DISABLED, $config->isTestMode());
        $this->assertSame(Config::CACHE_ENABLED, $config->getCache());
        $this->assertSame(Config::COMPRESSION_ENABLED, $config->getCompression());
    }

    public function testConstructor()
    {
        $config = new Config('sad', true, false, false);

        $this->assertSame('sad', $config->getAppId());
        $this->assertSame(Config::TESTMODE_ENABLED, $config->isTestMode());
        $this->assertSame(Config::CACHE_DISABLED, $config->getCache());
        $this->assertSame(Config::COMPRESSION_DISABLED, $config->getCompression());
    }

    public function testTestMode()
    {
        $config = new Config("asd123");

        $config->setTestMode(Config::TESTMODE_ENABLED);
        $this->assertSame(Config::TESTMODE_ENABLED, $config->isTestMode());

        $config->setTestMode(Config::TESTMODE_DISABLED);
        $this->assertSame(Config::TESTMODE_DISABLED, $config->isTestMode());
    }

    public function testCache()
    {
        $config = new Config("asd123");

        $config->setCache(Config::CACHE_DISABLED);
        $this->assertSame(Config::CACHE_DISABLED, $config->getCache());

        $config->setCache(Config::CACHE_ENABLED);
        $this->assertSame(Config::CACHE_ENABLED, $config->getCache());
    }

    public function testCompression()
    {
        $config = new Config("asd123");

        $config->setCompression(Config::COMPRESSION_DISABLED);
        $this->assertSame(Config::COMPRESSION_DISABLED, $config->getCompression());

        $config->setCompression(Config::COMPRESSION_ENABLED);
        $this->assertSame(Config::COMPRESSION_ENABLED, $config->getCompression());
    }

    public function testBaseUrl()
    {
        $config = new Config('sad');

        $config->setTestMode(Config::TESTMODE_ENABLED);
        $this->assertContains('test', $config->getBaseUrl());

        $config->setTestMode(Config::TESTMODE_DISABLED);
        $this->assertNotContains('test', $config->getBaseUrl());
    }

}
