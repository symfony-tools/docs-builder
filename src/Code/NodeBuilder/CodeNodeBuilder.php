<?php

namespace SymfonyDocsBuilder\Code\NodeBuilder;

use Doctrine\RST\Nodes\CodeNode;
use Symfony\Component\Console\Style\SymfonyStyle;
use SymfonyDocsBuilder\Code\BuildableDocument;
use SymfonyDocsBuilder\Code\ConsoleLoggerTrait;
use SymfonyDocsBuilder\Code\DiffApplier;
use SymfonyDocsBuilder\Code\Extractor\CodeSource;
use SymfonyDocsBuilder\Code\Extractor\PhpCodeExtractor;
use SymfonyDocsBuilder\Code\Extractor\TerminalExtractor;
use SymfonyDocsBuilder\Code\Extractor\YamlCodeExtractor;

class CodeNodeBuilder
{
    use ConsoleLoggerTrait;

    private $buildPath;
    private $terminalExtractor;
    private $phpCodeExtractor;
    private $yamlCodeExtractor;

    public function __construct(string $buildPath)
    {
        $this->buildPath = $buildPath;
        $this->terminalExtractor = new TerminalExtractor();
        $this->phpCodeExtractor = new PhpCodeExtractor();
        $this->yamlCodeExtractor = new YamlCodeExtractor();
    }

    public function build(CodeNode $node, BuildableDocument $doc)
    {
        $language = $this->determineLanguage($node);
        $diffApplier = new DiffApplier();
        $diffApplier->setConsoleLogger($this->consoleLogger);

        if (null === $language) {
            // not a real code block - doctrine/rst-parser#90
            return;
        }

        /** @var CodeNode $node */
        if ($language === 'terminal') {
            $this->logNote('terminal block');
            $commands = $this->terminalExtractor->getCommands($node->getValue());

            return;
        }

        if (strpos($language, 'diff') === 0) {
            $this->logNote('diff code block');

            if (strpos($language, '+') === false) {
                throw new \Exception(sprintf('Invalid diff code block language: use ::diff+lang in "%s"', $node->getValue()));
            }

            $language = strpos($language, '+') === false ? null : substr($language, 5);
            switch ($language) {
                case 'php':
                    $code = $this->phpCodeExtractor->extractCode($node->getValue());
                    $this->logNote(sprintf('Applying diff to "%s"', $code->getFilename()));
                    $newSource = $diffApplier->applyPhpDiff(
                        // the current contents
                        file_get_contents($this->getPath($code->getFilename())),
                        $node->getValue()
                    );
                    $this->setFileContents($code->getFilename(), $newSource, false);
                    break;
                default:
                    throw new \Exception(sprintf('Unsupported diff language "%s"', $language));
            }


            return;
        }

        if ($language === 'php') {
            $this->logNote('php code block');
            $phpCode = $this->phpCodeExtractor->extractCode($node->getValue());

            $this->setFileContents($phpCode->getFilename(), $phpCode->getCode());

            return;
        }

        if ($language === 'yaml') {
            $this->logNote('yaml code block');
            $phpCode = $this->yamlCodeExtractor->extractCode($node->getValue());

            $this->setFileContents($phpCode->getFilename(), $phpCode->getCode());

            return;
        }

        // TODO - handle other languages
        // but due to doctrine/rst-parser#90, some CodeNode objects
        // are *not* actually CodeNode

        $this->logDebug('Unhandled language: '.$language);
    }

    private function getPath(string $filename)
    {
        return $this->buildPath.'/'.$filename;
    }

    private function setFileContents(string $filename, string $contents, bool $complainAboutOverwrite = true)
    {
        if (file_exists($this->getPath($filename)) && $complainAboutOverwrite) {
            $this->logWarning(sprintf('Overwriting "%s" - considering using a diff - for code "%s"', $filename, $contents));
        }

        $this->logNote(sprintf(
            '%s file "%s"',
            file_exists($this->getPath($filename)) ? 'Overwriting' : 'Creating',
            $filename
        ));

        file_put_contents(
            $this->getPath($filename),
            $contents
        );
    }

    private function determineLanguage(CodeNode $node): ?string
    {
        if ($node->getLanguage()) {
            return $node->getLanguage();
        }

        // at this point, this is the "::" syntax, which should
        // mean it's a PHP code block. However, due to
        // doctrine/rst-parser#90, there is some over-matching

        // look specifically for some indication that this is php
        if (strpos($node->getValue(), '<?php') === 0 || strpos($node->getValue(), '// ') === 0) {
            return 'php';
        }

        return null;
    }
}