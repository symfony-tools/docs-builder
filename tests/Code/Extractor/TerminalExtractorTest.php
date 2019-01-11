<?php

namespace SymfonyDocsBuilder\Tests\Code\Extractor;

use PHPUnit\Framework\TestCase;
use SymfonyDocsBuilder\Code\Extractor\TerminalExtractor;

class TerminalExtractorTest extends TestCase
{
    /**
     * @dataProvider getTerminalTests
     */
    public function testGetCommands(string $terminalCode, array $expectedCommands)
    {
        $extractor = new TerminalExtractor();
        $actualCommands = $extractor->getCommands($terminalCode);

        $this->assertSame($expectedCommands, $actualCommands);
    }

    public function getTerminalTests()
    {
        yield [
            '$ composer install',
            ['composer install']
        ];

        yield [
            "\n$ composer install\n ",
            ['composer install']
        ];

        yield [
            <<<EOF
$ composer install

# optional: install the web server bundle (explained next)
$ cd my-project
$ composer require symfony/web-server-bundle --dev
EOF
            ,

            [
                'composer install',
                'cd my-project',
                'composer require symfony/web-server-bundle --dev'
            ]
        ];
    }
}
