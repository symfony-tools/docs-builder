<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Generator;

use Doctrine\RST\Environment;
use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Nodes\DocumentNode;

trait GeneratorTrait
{
    private function extractEnvironments(array $documents): array
    {
        return array_map(
            function (DocumentNode $document) {
                return $document->getEnvironment();
            },
            $documents
        );
    }

    private function getParserFilename(string $filePath, string $inputDir): string
    {
        return $parserFilename = str_replace([$inputDir.'/', '.html'], ['', ''], $filePath);
    }

    private function getEnvironment(array $environments, string $parserFilename): Environment
    {
        if (!isset($environments[$parserFilename])) {
            throw new \LogicException(sprintf('Cannot find environment for file "%s"', $parserFilename));
        }

        return $environments[$parserFilename];
    }

    private function getMeta(array $environments, string $parserFilename): MetaEntry
    {
        $environment = $this->getEnvironment($environments, $parserFilename);

        $allMetas = $environment->getMetas()->getAll();

        if (!isset($allMetas[$parserFilename])) {
            throw new \LogicException(sprintf('Cannot find metas for file "%s"', $parserFilename));
        }

        return $allMetas[$parserFilename];
    }
}
