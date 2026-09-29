<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit;

use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Config;
use Skaut\Skautis\InvalidArgumentException;

final class ConfigTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $config = new Config('asd123');

        self::assertSame('asd123', $config->getAppId());
        self::assertTrue($config->isTestMode());
        self::assertTrue($config->isCacheEnabled());
        self::assertTrue($config->isCompressionEnabled());
    }

    public function testEmptyAppIdIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config('');
    }

    public function testTestMode(): void
    {
        self::assertTrue((new Config('asd123', Config::TEST_MODE_ENABLED))->isTestMode());
        self::assertFalse((new Config('asd123', Config::TEST_MODE_DISABLED))->isTestMode());
    }

    public function testCache(): void
    {
        self::assertTrue((new Config('asd123', Config::TEST_MODE_ENABLED, Config::CACHE_ENABLED))->isCacheEnabled());
        self::assertFalse((new Config('asd123', Config::TEST_MODE_ENABLED, Config::CACHE_DISABLED))->isCacheEnabled());
    }

    public function testCompression(): void
    {
        $enabled = new Config('asd123', Config::TEST_MODE_ENABLED, Config::CACHE_DISABLED, Config::COMPRESSION_ENABLED);
        $disabled = new Config('asd123', Config::TEST_MODE_ENABLED, Config::CACHE_DISABLED, Config::COMPRESSION_DISABLED);

        self::assertTrue($enabled->isCompressionEnabled());
        self::assertFalse($disabled->isCompressionEnabled());
    }

    public function testBaseUrl(): void
    {
        self::assertSame('https://test-is.skaut.cz/', (new Config('sad', Config::TEST_MODE_ENABLED))->getBaseUrl());
        self::assertSame('https://is.skaut.cz/', (new Config('sad', Config::TEST_MODE_DISABLED))->getBaseUrl());
    }

    public function testSoapOptions(): void
    {
        $options = (new Config('app-id', Config::TEST_MODE_ENABLED, Config::CACHE_DISABLED, Config::COMPRESSION_DISABLED))->getSoapOptions();

        self::assertSame('app-id', $options['ID_Application']);
        self::assertSame(\SOAP_1_2, $options['soap_version']);
        self::assertSame(\WSDL_CACHE_NONE, $options['cache_wsdl']);
        self::assertArrayNotHasKey('compression', $options);
        self::assertIsResource($options['stream_context']);

        $withCache = (new Config('app-id', Config::TEST_MODE_ENABLED, Config::CACHE_ENABLED, Config::COMPRESSION_ENABLED))->getSoapOptions();

        self::assertSame(\WSDL_CACHE_BOTH, $withCache['cache_wsdl']);
        self::assertSame(\SOAP_COMPRESSION_ACCEPT | \SOAP_COMPRESSION_GZIP, $withCache['compression']);
    }
}
