<?php

namespace SymfonyDocsBuilder;

use Doctrine\RST\Builder;
use Symfony\Component\Console\Output\NullOutput;
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

        $builder = new Builder(KernelFactory::createKernel($config));
        $builder->build($config->getContentDir(), $config->getOutputDir());

        $buildResult = new BuildResult(
            $builder->getErrorManager()->getErrors(),
            $builder
        );

        $missingFilesChecker = new MissingFilesChecker($config);
        $missingFiles = $missingFilesChecker->getMissingFiles();
        foreach ($missingFiles as $missingFile) {
            $buildResult->appendError(sprintf('Missing file "%s"', $missingFile));
        }

        if (!$buildResult->isSuccessful()) {
            $buildResult->prependError(sprintf('Build errors from "%s"', date('Y-m-d h:i:s')));
            $filesystem->dumpFile($config->getOutputDir().'/build_errors.txt', implode("\n", $buildResult->getErrors()));
        }

        $metas = $buildResult->getMetas();
        if ($config->getSubdirectoryToBuild()) {
            $htmlForPdfGenerator = new HtmlForPdfGenerator($metas, $config);
            $htmlForPdfGenerator->generateHtmlForPdf();
        } else {
            $jsonGenerator = new JsonGenerator($metas, $config);
            $buildResult->setJsonResults($jsonGenerator->generateJson($builder->getIndexName()));
        }

        return $buildResult;
    }
}
