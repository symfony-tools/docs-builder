<?php

namespace SymfonyDocsBuilder\Tests\Code\Extractor;

use PHPUnit\Framework\TestCase;
use SymfonyDocsBuilder\Code\Extractor\PhpCodeExtractor;

class PhpCodeExtractorTest extends TestCase
{
    /**
     * @dataProvider getCodeTests
     */
    public function testExtractCode(string $source, string $expectedFilename, string $expectedCode)
    {
        $extractor = new PhpCodeExtractor();
        $phpCode = $extractor->extractCode($source);

        $this->assertSame($expectedFilename, $phpCode->getFilename());
        $this->assertSame($expectedCode, $phpCode->getCode());
    }

    public function getCodeTests()
    {
        yield 'code_block_with_opening_tag' => [
            <<<EOF
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class LuckyController
{
    public function number()
    {
        \$number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.\$number.'</body></html>'
        );
    }
}
EOF
            ,
            'src/Controller/LuckyController.php',
            <<<EOF
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class LuckyController
{
    public function number()
    {
        \$number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.\$number.'</body></html>'
        );
    }
}
EOF
            ]
        ;

        yield 'no_opening_tag' => [
            <<<EOF
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class LuckyController
{
    public function number()
    {
        \$number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.\$number.'</body></html>'
        );
    }
}
EOF
            ,
            'src/Controller/LuckyController.php',
            <<<EOF
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class LuckyController
{
    public function number()
    {
        \$number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.\$number.'</body></html>'
        );
    }
}
EOF
            ]
        ;
    }
}
