<?php

namespace SymfonyDocsBuilder\Tests\Release;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use SymfonyDocsBuilder\Phar\Compiler;
use SymfonyDocsBuilder\Release\Releaser;

class ReleaserTest extends TestCase
{
    public function setUp(): void
    {
        if (file_exists($pharFile = __DIR__.'/../../docs.phar')) {
            unlink($pharFile);
        }
        touch($pharFile);
    }

    public function tearDown(): void
    {
        unlink( __DIR__.'/../../docs.phar');
    }

    public function testCreateReleaseFailsWithInvalidTag(): void
    {
        $releaser = new Releaser(new MockHttpClient(), $compiler = $this->createMock(Compiler::class));

        $compiler->expects($this->never())->method('compile');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"invalid tag" is not a valid tag.');

        $releaser->createRelease('invalid tag');
    }

    public function testCreateRelease(): void
    {
        $callback = static function ($method, $url) {
            switch (true) {
                case 'POST' === $method
                    && 'https://api.github.com/repos/nikophil/test/releases' === $url:
                    return new MockResponse('{"id":1}');
                case 'POST' === $method
                    && 'https://uploads.github.com/repos/nikophil/test/releases/1/assets?name=docs.phar' === $url:
                case 'PATCH' === $method
                    && 'https://api.github.com/repos/nikophil/test/releases/1' === $url:
                    return new MockResponse();
            }

            self::fail(sprintf("Unexpected request:\n- method: %s\n- url: %s", $method, $url));
        };

        $releaser = new Releaser(new MockHttpClient($callback), $compiler = $this->createMock(Compiler::class));

        $compiler->expects($this->once())->method('compile');

        $releaser->createRelease('v1.0.0');
    }

    public function testCreateReleaseThrowExceptionIf401IsReturned(): void
    {
        $releaser = new Releaser(new MockHttpClient([new MockResponse('', ['http_code' => 401])]), $this->createMock(Compiler::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error while trying to create release: Invalid token.');

        $releaser->createRelease('v1.0.0');
    }

    public function testReleaseIsDeletedIfAddAssetReturnsAnError(): void
    {
        $callback = static function ($method, $url) {
            switch (true) {
                case 'POST' === $method
                    && 'https://api.github.com/repos/nikophil/test/releases' === $url:
                    return new MockResponse('{"id":1}');
                case 'POST' === $method
                    && 'https://uploads.github.com/repos/nikophil/test/releases/1/assets?name=docs.phar' === $url:
                    return new MockResponse('', ['http_code' => 500]);
                case 'DELETE' === $method
                    && 'https://api.github.com/repos/nikophil/test/releases/1' === $url:
                    return new MockResponse();
            }

            self::fail(sprintf("Unexpected request:\n- method: %s\n- url: %s", $method, $url));
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error while adding asset to release.');

        $releaser = new Releaser(new MockHttpClient($callback), $this->createMock(Compiler::class));

        $releaser->createRelease('v1.0.0');
    }
}