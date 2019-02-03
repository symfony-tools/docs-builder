<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Generator;

use Doctrine\RST\Environment;
use Doctrine\RST\Meta\CachedMetasLoader;
use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Meta\Metas;
use Doctrine\RST\Nodes\DocumentNode;
use SymfonyDocsBuilder\BuildContext;

trait GeneratorTrait
{
    private $buildContext;

    /** @var Environment[] */
    private $environments;
    /** @var Metas */
    private $cachedMetas;

    public function __construct(BuildContext $buildContext)
    {
        $this->buildContext = $buildContext;
    }

    private function extractEnvironmentsAndCachedMetas(array $documents): void
    {
        $this->environments = array_map(
            function (DocumentNode $document) {
                return $document->getEnvironment();
            },
            $documents
        );

        if ($this->buildContext->getDisableCache()) {
            return;
        }

        $this->cachedMetas = new Metas();
        $cachedMetasLoader = new CachedMetasLoader();
        $cachedMetasLoader->loadCachedMetaEntries($this->buildContext->getHtmlOutputDir(), $this->cachedMetas);
    }

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

    private function loadMetaFromCache(string $parserFilename): MetaEntry
    {
        $meta = $this->cachedMetas->get($parserFilename);

        if (!$meta) {
            throw new \LogicException(sprintf('Cannot find cached meta for file "%s"', $parserFilename));
        }

        return $meta;
    }

    private function getMeta(string $parserFilename): MetaEntry {
        if ($this->useCacheForFile($parserFilename)) {
            return $this->loadMetaFromCache($parserFilename);
        }

        $environment = $this->getEnvironment($parserFilename);

        $allMetas = $environment->getMetas()->getAll();

        if (!isset($allMetas[$parserFilename])) {
            throw new \LogicException(sprintf('Cannot find metas for file "%s"', $parserFilename));
        }

        return $allMetas[$parserFilename];
    }

    /**
     * @param Environment[] $environments
     */
    private function useCacheForFile(string $parserFilename): bool
    {
        if ($this->buildContext->getDisableCache()) {
            return false;
        }

        // if an environment exits for the given file, it means that the file has been built from the parser
        // thus it was not loaded from cache.
        return !isset($this->environments[$parserFilename]);
    }
}
