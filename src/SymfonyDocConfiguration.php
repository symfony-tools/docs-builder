<?php

namespace SymfonyDocs;

class SymfonyDocConfiguration
{
    /** @var array */
    private static $configuration;

    public static function getSymfonyDocConfiguration(): array
    {
        if (null === self::$configuration) {
            self::$configuration = json_decode(file_get_contents(__DIR__.'/../conf.json'), true);
        }

        return self::$configuration;
    }

    public static function getVersion(): string
    {
        if (!isset(self::getSymfonyDocConfiguration()['version'])) {
            throw new \RuntimeException('The version must be defined in "/_build/conf.json"');
        }

        return self::getSymfonyDocConfiguration()['version'];
    }

    public static function getSymfonyApiUrl(): string
    {
        if (!isset(self::getSymfonyDocConfiguration()['symfony_api_url'])) {
            throw new \RuntimeException('The "symfony_api_url" must be defined in "/_build/conf.json"');
        }

        return self::getSymfonyDocConfiguration()['symfony_api_url'];
    }

    public static function getPhpDocUrl(): string
    {
        if (!isset(self::getSymfonyDocConfiguration()['php_doc_url'])) {
            throw new \RuntimeException('The "php_doc_url" must be defined in "/_build/conf.json"');
        }

        return self::getSymfonyDocConfiguration()['php_doc_url'];
    }
}
