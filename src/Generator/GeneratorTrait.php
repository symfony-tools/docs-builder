<?php declare(strict_types=1);

namespace SymfonyDocs\Generator;

use Doctrine\RST\Environment;
use Doctrine\RST\Meta\MetaEntry;

trait GeneratorTrait
{
    private function getParserFilename(string $filePath, string $inputDir): string
    {
        return $parserFilename = str_replace([$inputDir.'/', '.html'], ['', ''], $filePath);
    }

    private function getEnvironment(string $parserFilename): Environment
    {
        if (!isset($this->environments[$parserFilename])) {
            throw new \LogicException(sprintf('Cannot find environment for file "%s"', $parserFilename));
        }

        return $this->environments[$parserFilename];
    }

    private function getMeta(string $parserFilename): MetaEntry
    {
        $environment = $this->getEnvironment($parserFilename);

        $allMetas = $environment->getMetas()->getAll();

        if (!isset($allMetas[$parserFilename])) {
            throw new \LogicException(sprintf('Cannot find metas for file "%s"', $parserFilename));
        }

        return $allMetas[$parserFilename];
    }
}
