<?php

namespace SymfonyDocsBuilder\Code;

use Doctrine\RST\Event\PostNodeCreateEvent;
use Doctrine\RST\Kernel;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Parser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use SymfonyDocsBuilder\Code\NodeBuilder\CodeNodeBuilder;

class DocumentCodeBuilder
{
    use ConsoleLoggerTrait;

    private $kernel;
    private $buildDirectory;
    private $fs;

    /**
     * Temporarily stores the nodes for retrieval later.
     */
    private $nodes = [];

    public function __construct(Kernel $kernel, string $buildDirectory)
    {
        $this->kernel = $kernel;
        $this->buildDirectory = $buildDirectory;
        $this->fs = new Filesystem();
    }

    public function createBuildableDocument(string $filename)
    {
        $path = realpath($filename);
        if (!file_exists($path)) {
            throw new \Exception(sprintf('Cannot find file "%s"', $filename));
        }
        $currentDir = dirname($path);

        // set up the listener, parse, then remove the listener
        $this->kernel->getConfiguration()->getEventManager()
            ->addEventListener(PostNodeCreateEvent::POST_NODE_CREATE, $this);
        $parser = new Parser($this->kernel);
        $parser->getEnvironment()->setCurrentFileName($filename);
        $parser->getEnvironment()->setCurrentDirectory($currentDir);
        $documentNode = $parser->parseFile($path);
        $this->kernel->getConfiguration()->getEventManager()
            ->removeEventListener(PostNodeCreateEvent::POST_NODE_CREATE, $this);

        $nodes = $this->nodes;
        $this->nodes = [];

        return new BuildableDocument($filename, $nodes);
    }

    public function buildDocument(BuildableDocument $doc) {
        $buildPath = $this->buildDirectory.'/'.$doc->getFilename();

        $codeNodeBuilder = new CodeNodeBuilder($buildPath);
        $codeNodeBuilder->setConsoleLogger($this->consoleLogger);
        $gitUtil = new GitUtil();
        $gitUtil->setConsoleLogger($this->consoleLogger);

        if (file_exists($buildPath)) {
            $this->logNote(sprintf('Clearing build directory "%s"', $buildPath));
            $this->fs->remove($buildPath);
        }

        // TODO - handle pre-requisite document
        // right now we always assume a blank flex install as the pre-req
        if (!$this->fs->exists($this->getFlexPath())) {
            $this->buildFlexSkeleton($gitUtil);
        }

        $this->startDocumentApp($this->getFlexPath(), $buildPath, $gitUtil);

        $this->fs->mkdir($buildPath);

        $this->logDebug('Building Nodes');
        foreach ($doc->getNodes() as $node) {
            if (!$node instanceof CodeNode) {
                continue;
            }

            $codeNodeBuilder->build($node, $doc);
        }
    }

    public function postNodeCreate(PostNodeCreateEvent $event)
    {
        $this->nodes[] = $event->getNode();
    }

    private function getFlexPath()
    {
        return $this->buildDirectory.'/flex_project';
    }

    private function buildFlexSkeleton(GitUtil $gitUtil)
    {
        $this->logNote('Building Flex skeleton');
        $process = new Process(['composer', 'create-project', 'symfony/skeleton', $this->getFlexPath(), '--prefer-dist', '--no-progress']);
        $this->mustRun($process);

        $gitUtil->init($this->getFlexPath());

        $gitUtil->addGitAuthor($this->getFlexPath());

        $gitUtil->commit($this->getFlexPath(), 'Stock Flex app');
    }

    private function startDocumentApp(string $flexPath, string $targetBuildPath, GitUtil $gitUtil)
    {
        try {
            $gitUtil->clone($flexPath, $targetBuildPath);

            // install dependencies
            $process = new Process(['composer', 'install'], $targetBuildPath);
            $this->mustRun($process);

            $gitUtil->addGitAuthor($targetBuildPath);
        } catch (ProcessFailedException $e) {
            $this->fs->remove($targetBuildPath);

            throw $e;
        }
    }

    private function mustRun(Process $process)
    {
        $this->logDebug('Command: '.$process->getCommandLine());
        $process->mustRun();
    }
}
