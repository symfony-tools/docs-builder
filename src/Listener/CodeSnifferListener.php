<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Listener;

use Doctrine\RST\Event\PostNodeCreateEvent;
use Doctrine\RST\Event\PostNodeRenderEvent;
use Doctrine\RST\Event\PostParseDocumentEvent;
use Doctrine\RST\Event\PreNodeRenderEvent;
use Doctrine\RST\Nodes\CodeNode;
use PhpCsFixer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use SymfonyDocsBuilder\Renderers\CodeNodeRenderer;

class CodeSnifferListener
{
    private $application;

    public function __construct()
    {
        $this->application = new Application();
        $this->application->setAutoExit(false);
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
            throw new \RuntimeException(
                sprintf(
                    "Found malformed code:\n%s\n\nReasons:\n%s",
                    $code,
                    json_encode($content['files'][0]['appliedFixers'])
                )
            );
        }
    }

    /**
     * Problems:
     *  - $node->getLanguage() is always null
     *  - $environment is not reachable > we cannot inform the user where the error is coming from
     */
    public function postNodeCreate(PostNodeCreateEvent $postNodeCreateEvent)
    {
//        $node = $postNodeCreateEvent->getNode();
//        if (!$node instanceof CodeNode) {
//            return;
//        }
//
//        dump($node->getLanguage());
    }

    /**
     * Problem:
     *  - Sometimes (?) $node->getLanguage() is null (and sadly, it's often php...)
     *  - we would need to extract all CodeNodes recursively
     */
    public function postParseDocument(PostParseDocumentEvent $postParseDocumentEvent)
    {
//        $documentNode = $postParseDocumentEvent->getDocumentNode();
//        $nodes = $documentNode->getNodes();
//
//        foreach ($nodes as $node) {
//            if (!$node instanceof CodeNode) {
//                continue;
//            }
//
//            dump($node->getLanguage());
//        }
    }

    /**
     * Same as postNodeRender: environment is not available
     */
    public function preNodeRender(PreNodeRenderEvent $preNodeRenderEvent)
    {
//        $node = $preNodeRenderEvent->getNode();
//        if (!$node instanceof CodeNode) {
//            return;
//        }
//
//        dump($node->getLanguage());
    }
}
