<?php

declare(strict_types=1);

namespace Skaut\Skautis;

/**
 * Immutable configuration of one skautIS application.
 */
final readonly class Config
{
    public const bool CACHE_ENABLED = true;
    public const bool CACHE_DISABLED = false;

    public const bool TEST_MODE_ENABLED = true;
    public const bool TEST_MODE_DISABLED = false;

    public const bool COMPRESSION_ENABLED = true;
    public const bool COMPRESSION_DISABLED = false;

    private const string URL_TEST = 'https://test-is.skaut.cz/';
    private const string URL_PRODUCTION = 'https://is.skaut.cz/';

    /**
     * @param string $appId       ID aplikace přidělené správcem skautISu
     * @param bool   $testMode    používat testovací skautIS?
     * @param bool   $cache       cachovat WSDL?
     * @param bool   $compression komprimovat SOAP požadavky?
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string $appId,
        private bool $testMode = self::TEST_MODE_ENABLED,
        private bool $cache = self::CACHE_ENABLED,
        private bool $compression = self::COMPRESSION_ENABLED,
    ) {
        if ($appId === '') {
            throw new InvalidArgumentException('AppId cannot be empty.');
        }
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function isCacheEnabled(): bool
    {
        return $this->cache;
    }

    public function isCompressionEnabled(): bool
    {
        return $this->compression;
    }

    public function getBaseUrl(): string
    {
        return $this->testMode ? self::URL_TEST : self::URL_PRODUCTION;
    }

    /**
     * Options for SoapClient. They are not user-editable so that every request stays valid for the skautIS API.
     *
     * @return array<string, mixed>
     */
    public function getSoapOptions(): array
    {
        $soapOptions = [
            'ID_Application' => $this->appId,
            'soap_version' => \SOAP_1_2,
            'encoding' => 'utf-8',
            'stream_context' => stream_context_create([
                'ssl' => ['crypto_method' => \STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT],
            ]),
            'exceptions' => true,
            'trace' => true,
            'user_agent' => 'Skautis PHP library',
            'keep_alive' => true,
            'cache_wsdl' => $this->cache ? \WSDL_CACHE_BOTH : \WSDL_CACHE_NONE,
        ];

        if ($this->compression) {
            $soapOptions['compression'] = \SOAP_COMPRESSION_ACCEPT | \SOAP_COMPRESSION_GZIP;
        }

        return $soapOptions;
    }
}
