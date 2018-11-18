<?php declare(strict_types=1);

namespace SymfonyDocs\Generator;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\DocumentNode;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class HtmlForPdfGenerator
 */
class HtmlForPdfGenerator
{
    use GeneratorTrait;

    /** @var Environment[] */
    private $environments;

    public function __construct(array $documents)
    {
        $this->environments = array_map(
            function (DocumentNode $document) {
                return $document->getEnvironment();
            },
            $documents
        );
    }

    public function generateHtmlForPdf(string $htmlDir, string $parseOnly/*, ProgressBar $progressBar*/)
    {
        $finder = new Finder();
        $finder->in($htmlDir)
            ->depth(0)
            ->notName($parseOnly);

        $fs = new Filesystem();
        foreach ($finder as $file) {
            $fs->remove($file->getRealPath());
        }

        $basePath  = sprintf('%s/%s', $htmlDir, $parseOnly);
        $indexFile = sprintf('%s/%s', $basePath, 'index.html');
        if (!$fs->exists($indexFile)) {
            throw new \InvalidArgumentException('File "%s" does not exist', $indexFile);
        }

        $parserFilename = $this->getParserFilename($indexFile, $htmlDir);
        $meta           = $this->getMeta($parserFilename);
        dump(current($meta->getTocs()));
    }
}
