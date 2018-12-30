<?php

namespace SymfonyDocsBuilder\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetsExtension extends AbstractExtension
{
    /** @var string */
    private $htmlOutputDir;

    public function __construct(string $htmlOutputDir)
    {
        $this->htmlOutputDir = $htmlOutputDir;
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this, 'asset'], ['is_safe' => ['html']]),
        ];
    }

    public function asset($path)
    {
        return sprintf('%s/assets/%s', $this->htmlOutputDir, $path);
    }
}
