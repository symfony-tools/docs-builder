<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Tests\Reference;

use Doctrine\RST\Configuration;
use Doctrine\RST\Environment;
use PHPUnit\Framework\TestCase;
use SymfonyDocsBuilder\BuildConfig;
use SymfonyDocsBuilder\Reference\ClassReference;

class ClassReferenceTest extends TestCase
{
    /**
     * @dataProvider getDefaultVersionTests
     */
    public function testResolveWithTheDefaultVersions(string $className, string $expectedUrl)
    {
        $this->assertSame($expectedUrl, $this->resolve(new BuildConfig(), $className));
    }

    public function getDefaultVersionTests()
    {
        yield 'symfony_ai' => [
            'Symfony\AI\Agent\Memory\StaticMemoryProvider',
            'https://github.com/symfony/ai/blob/main/src/agent/src/Memory/StaticMemoryProvider.php',
        ];
        yield 'symfony_ux' => [
            'Symfony\UX\Chartjs\Twig\ChartExtension',
            'https://github.com/symfony/ux/blob/2.x/src/Chartjs/src/Twig/ChartExtension.php',
        ];
    }

    /**
     * @dataProvider getConfiguredVersionTests
     */
    public function testResolveWithTheConfiguredVersions(string $className, string $expectedUrl)
    {
        $buildConfig = (new BuildConfig())
            ->setSymfonyVersion('7.4')
            ->setSymfonyAiVersion('1.0')
            ->setSymfonyUxVersion('3.x');

        $this->assertSame($expectedUrl, $this->resolve($buildConfig, $className));
    }

    public function getConfiguredVersionTests()
    {
        yield 'symfony' => [
            'Symfony\Component\HttpKernel\Kernel',
            'https://github.com/symfony/symfony/blob/7.4/src/Symfony/Component/HttpKernel/Kernel.php',
        ];
        yield 'symfony_ai' => [
            'Symfony\AI\Agent\Memory\StaticMemoryProvider',
            'https://github.com/symfony/ai/blob/1.0/src/agent/src/Memory/StaticMemoryProvider.php',
        ];
        yield 'symfony_ai_bundle' => [
            'Symfony\AI\AiBundle\Security\Attribute\IsGrantedTool',
            'https://github.com/symfony/ai/blob/1.0/src/ai-bundle/src/Security/Attribute/IsGrantedTool.php',
        ];
        yield 'symfony_ux' => [
            'Symfony\UX\Chartjs\Twig\ChartExtension',
            'https://github.com/symfony/ux/blob/3.x/src/Chartjs/src/Twig/ChartExtension.php',
        ];
    }

    private function resolve(BuildConfig $buildConfig, string $className): string
    {
        $reference = new ClassReference(
            $buildConfig->getSymfonyRepositoryUrl(),
            $buildConfig->getSymfonyAiRepositoryUrl(),
            $buildConfig->getSymfonyUxRepositoryUrl()
        );

        return $reference->resolve(new Environment(new Configuration()), $className)->getUrl();
    }
}
