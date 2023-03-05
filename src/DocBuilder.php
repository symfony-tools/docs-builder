<?php

namespace SymfonyDocsBuilder;

use Doctrine\RST\Builder;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\CI\MissingFilesChecker;
use SymfonyDocsBuilder\Generator\HtmlForPdfGenerator;
use SymfonyDocsBuilder\Generator\JsonGenerator;

final class DocBuilder
{
    public function build(BuildConfig $config): BuildResult
    {
        $filesystem = new Filesystem();
        if (!$config->isBuildCacheEnabled() && $filesystem->exists($config->getOutputDir())) {
            $filesystem->remove($config->getOutputDir());
        }
        $filesystem->mkdir($config->getOutputDir());

        $configFileParser = new ConfigFileParser($config, new NullOutput());
        $configFileParser->processConfigFile($config->getContentDir());

        $builder = BuilderFactory::createBuilder($config);
        $builder->build($config->getContentDir(), $config->getOutputDir());

        $buildResult = new BuildResult($builder);

        $missingFilesChecker = new MissingFilesChecker($config);
        $missingFiles = $missingFilesChecker->getMissingFiles();
        foreach ($missingFiles as $missingFile) {
            $buildResult->appendError(sprintf('Missing file "%s"', $missingFile));
        }

        if (!$buildResult->isSuccessful()) {
            $buildResult->prependError(sprintf('Build errors from "%s"', date('Y-m-d h:i:s')));
            $filesystem->dumpFile($config->getOutputDir().'/build_errors.txt', implode("\n", $buildResult->getErrors()));
        }

        if ($config->isContentAString()) {
            $htmlFilePath = $config->getOutputDir().'/index.html';
            if (is_file($htmlFilePath)) {
                // generated HTML contents are a full HTML page, so we need to
                // extract the contents of the <body> tag
                $crawler = new Crawler(file_get_contents($htmlFilePath));
                $buildResult->setStringResult(trim($crawler->filter('body')->html()));
            }
        } elseif ($config->getSubdirectoryToBuild()) {
            $metas = $buildResult->getMetadata();
            $htmlForPdfGenerator = new HtmlForPdfGenerator($metas, $config);
            $htmlForPdfGenerator->generateHtmlForPdf();
        } elseif ($config->generateJsonFiles()) {
            $metas = $buildResult->getMetadata();
            $jsonGenerator = new JsonGenerator($metas, $config);
            $buildResult->setJsonResults($jsonGenerator->generateJson($builder->getConfiguration()->getIndexFileName()));
        }

        return $buildResult;
    }

    public function buildString(string $contents): BuildResult
    {
        $filesystem = new Filesystem();
        $tmpDir = sys_get_temp_dir().'/doc_builder_build_string_'.random_int(1, 100000000);
        if ($filesystem->exists($tmpDir)) {
            $filesystem->remove($tmpDir);
        }
        $filesystem->mkdir($tmpDir);

        $filesystem->dumpFile($tmpDir.'/index.rst', $contents);

        $buildConfig = (new BuildConfig())
            ->setContentIsString()
            ->setContentDir($tmpDir)
            ->setOutputDir($tmpDir.'/output')
            ->disableBuildCache()
            ->disableJsonFileGeneration()
        ;

        $buildResult = $this->build($buildConfig);
        $filesystem->remove($tmpDir);

        return $buildResult;
    }
}
