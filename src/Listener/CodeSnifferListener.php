<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Listener;

use Doctrine\RST\Event\PostNodeRenderEvent;
use Doctrine\RST\Nodes\CodeNode;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Console\Application;
use PhpCsFixer\FixerFactory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use SymfonyDocsBuilder\CI\CodeSniffer\CodeSnifferViolationsList;
use SymfonyDocsBuilder\Renderers\CodeNodeRenderer;

class CodeSnifferListener
{
    private $application;
    private $fixerFactory;
    private $fixers;
    private $codeSnifferViolationsList;

    public function __construct(CodeSnifferViolationsList $codeSnifferViolationsList)
    {
        $this->application = new Application();
        $this->application->setAutoExit(false);

        $this->fixerFactory = new FixerFactory();
        $this->fixerFactory->registerBuiltInFixers();

        $this->codeSnifferViolationsList = $codeSnifferViolationsList;
    }

    /**
     * Problems:
     *  - $environment is not reachable > we cannot inform the user where the error is coming from
     */
    public function postNodeRender(PostNodeRenderEvent $postNodeRenderEvent)
    {
        $node = $postNodeRenderEvent->getRenderedNode()->getNode();
        if (!$node instanceof CodeNode) {
            return;
        }

        $language = $node->getLanguage();

        if ('php' !== (CodeNodeRenderer::LANGUAGES_MAPPING[$language] ?? $language)) {
            return;
        }

        $code = $node->getValue();

        $tempFile = tempnam(sys_get_temp_dir(), 'symfony-docs-builder').'.php';
        file_put_contents($tempFile, $code);

        $input = new ArrayInput(
            [
                'command'   => 'fix',
                'path'      => [$tempFile],
                '--format'  => 'json',
                '--dry-run' => true,
                '-vvv'      => true,
            ]
        );

        $output = new BufferedOutput();

        $this->application->run($input, $output);

        $content = json_decode($output->fetch(), true);

        if (count($content['files']) === 1) {
            $this->codeSnifferViolationsList->add(
                $code,
                array_map([$this, 'getFixerSummary'], $content['files'][0]['appliedFixers'])
            );
        }
    }

    /**
     * @return AbstractFixer[]
     */
    private function getFixers(): array
    {
        if (null !== $this->fixers) {
            return $this->fixers;
        }

        $fixers = [];
        foreach ($this->fixerFactory->getFixers() as $fixer) {
            $fixers[$fixer->getName()] = $fixer;
        }

        $this->fixers = $fixers;
        ksort($this->fixers);

        return $this->fixers;
    }

    private function getFixerSummary($name)
    {
        $fixers = $this->getFixers();

        $fixer = $fixers[$name];

        return sprintf("    - %s", $fixer->getDefinition()->getSummary());
    }
}
