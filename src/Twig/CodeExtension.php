<?php

namespace SymfonyTools\GuidesExtension\Twig;

use SymfonyTools\GuidesExtension\Build\BuildConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CodeExtension extends AbstractExtension
{
    public function __construct(
        private BuildConfig $buildConfig
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('fqcn', $this->fqcn(...), ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('dump', dd(...)),
        ];
    }

    public function fqcn(string $fqcn): string
    {
        // some browsers can't break long <code> properly, so we inject a
        // `<wbr>` (word-break HTML tag) after some characters to help break those
        // We only do this for very long <code> (4 or more \\) to not break short
        // and common `<code>` such as App\Entity\Something
        if (substr_count($fqcn, '\\') >= 4) {
            // breaking before the backslask is what Firefox browser does
            $fqcn = str_replace('\\', '<wbr>\\', $fqcn);
        }

        return $fqcn;
    }
}
