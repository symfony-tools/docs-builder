<?php

namespace SymfonyDocsBuilder\Tests\Code;

use PHPUnit\Framework\TestCase;
use SymfonyDocsBuilder\Code\DiffApplier;
use SymfonyDocsBuilder\Code\Extractor\PhpCodeExtractor;

class DiffApplierTest extends TestCase
{
    /**
     * @dataProvider getPhpTests
     */
    public function testApplyPhpDiff(string $startingSource, string $diff, string $expectedCode)
    {
        $applier = new DiffApplier();
        $actualCode = $applier->applyPhpDiff($startingSource, $diff);

        $this->assertSame($expectedCode, $actualCode);
    }

    public function getPhpTests()
    {
        yield 'add_use_statement_alphabetical' => [
            <<<EOF
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class LuckyController
{
}
EOF
            ,
            <<<EOF
// src/Controller/LuckyController.php

// ...
+ use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
+ use Symfony\Component\Routing\Annotation\Route;
+ use Symfony\Component\Validator\Validator\ValidatorInterface;

class LuckyController
{
}
EOF
            ,

            <<<EOF
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

class LuckyController
{
}
EOF
            ]
        ;

        yield 'add_first_use_statement' => [
            <<<EOF
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

class LuckyController
{
}
EOF
            ,
            <<<EOF
// src/Controller/LuckyController.php

// ...
+ use Symfony\Component\Routing\Annotation\Route;

class LuckyController
{
}
EOF
            ,

            <<<EOF
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class LuckyController
{
}
EOF
            ]
        ;

        yield 'add_at_beginning_of_class' => [
            <<<EOF
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

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
            <<<EOF
// src/Controller/LuckyController.php

// ...
+ use Symfony\Component\Routing\Annotation\Route;

class LuckyController
{
+     /**
+      * @Route("/lucky/number")
+      */
    public function number()
    {
        // ... this looks exactly the same
    }
}
EOF
            ,

            <<<EOF
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

class LuckyController
{
    /**
     * @Route("/lucky/number")
     */
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

        yield 'replace_class_line' => [
            <<<EOF
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class LuckyController
{
}
EOF
            ,
            <<<EOF
// src/Controller/LuckyController.php

// ...
+ use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

- class LuckyController
+ class LuckyController extends AbstractController
{
    // ...
}
EOF
            ,

            <<<EOF
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class LuckyController extends AbstractController
{
}
EOF
            ]
        ;

        // try adding use statement with no use statements
        // does + and - lines need to be in a certain order?
    }
}
