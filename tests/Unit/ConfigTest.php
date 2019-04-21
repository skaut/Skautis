<?php

namespace Test\Skautis;

use Skautis\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultConfiguration(): void
    {
        $config = new Config('asd123');

        $this->assertEquals('asd123', $config->getAppId());
        $this->assertTrue($config->isTestMode());
        $this->assertTrue($config->isCacheEnabled());
        $this->assertTrue($config->isCompressionEnabled());
    }

    public function testTestModeEnabled(): void
    {
        $config = new Config('asd123', Config::TEST_MODE_ENABLED);
        $this->assertTrue($config->isTestMode());
    }

    public function testTestModeDisabled(): void
    {
        $config = new Config('asd123', Config::TEST_MODE_DISABLED);
        $this->assertFalse($config->isTestMode());
    }

    public function testCacheEnabled(): void
    {
        $config = new Config('asd123', Config::TEST_MODE_ENABLED, Config::CACHE_ENABLED);
        $this->assertTrue($config->isCacheEnabled());
    }

    public function testCacheDisabled(): void
    {
        $config = new Config('asd123', Config::TEST_MODE_ENABLED, Config::CACHE_DISABLED);
        $this->assertFalse($config->isCacheEnabled());
    }

    public function testCompressionEnabled(): void
    {
        $config = new Config('asd123', Config::TEST_MODE_ENABLED, Config::CACHE_DISABLED, Config::COMPRESSION_ENABLED);
        $this->assertTrue( $config->isCompressionEnabled());
    }

    public function testCompressionDisabled(): void
    {
        $config = new Config('asd123', Config::TEST_MODE_ENABLED, Config::CACHE_DISABLED, Config::COMPRESSION_DISABLED);
        $this->assertFalse( $config->isCompressionEnabled());
    }

    public function testBaseUrlTestModeEnabled(): void
    {
        $config = new Config('sad', Config::TEST_MODE_ENABLED);
        $this->assertStringStartsWith('https://test', $config->getBaseUrl());
    }

    public function testBaseUrlTestModeDisabled(): void
    {
        $config = new Config('sad', Config::TEST_MODE_DISABLED);
        $this->assertStringStartsWith('https://is.', $config->getBaseUrl());
    }
}
