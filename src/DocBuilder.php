<?php

namespace SymfonyDocsBuilder;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\CI\MissingFilesChecker;
use SymfonyDocsBuilder\Generator\HtmlForPdfGenerator;
use SymfonyDocsBuilder\Generator\JsonGenerator;

final class DocBuilder
{
    private $useBuildCache = true;
    private $symfonyVersion = '4.4';
    private $theme = Configuration::THEME_DEFAULT;// 'rtd'; // also: Configuration::THEME_DEFAULT
    private const PHP_DOC_URL = 'https://secure.php.net/manual/en';
    private const SYMFONY_API_URL = 'https://api.symfony.com/{symfonyVersion}';
    private const SYMFONY_DOC_URL = 'https://symfony.com/doc/{symfonyVersion}';

    public function buildDir(string $rstContentsDir/*, BuildConfig $config*/, string $outputDir, string $publicImagesDir = null, string $publicImagesPrefix = null): BuildResult
    {
        return $this->doBuild($rstContentsDir, $outputDir, $publicImagesDir ?? $outputDir.'/_images', $publicImagesPrefix ?? $publicImagesPrefix.'/_images');
    }

    public function buildDocument(string $rstContent, BuildConfig $config): BuildResult
    {
        // TODO
    }

    private function doBuild(string $rstContentsDir, string $htmlOutputDir, string $publicImagesDir, string $publicImagesPrefix, ?string $rstBookSubDir = null): BuildResult
    {
        if (!file_exists($rstContentsDir)) {
            throw new \InvalidArgumentException(sprintf('RST contents directory "%s" does not exist', $rstContentsDir));
        }

        $filesystem = new Filesystem();
        if (!$this->useBuildCache && $filesystem->exists($htmlOutputDir)) {
            $filesystem->remove($htmlOutputDir);
        }
        $filesystem->mkdir($htmlOutputDir);

        if ($rstBookSubDir && !file_exists($rstContentsDir.'/'.$rstBookSubDir)) {
            throw new \InvalidArgumentException(sprintf('Given book directory "%s" is not a subdirectory of the RST contents dir "%s".', $rstBookSubDir, $rstContentsDir));
        }

        $buildContext = new BuildContext(
            $this->symfonyVersion,
            str_replace('{symfonyVersion}', $this->symfonyVersion, self::SYMFONY_API_URL),
            self::PHP_DOC_URL,
            str_replace('{symfonyVersion}', $this->symfonyVersion, self::SYMFONY_DOC_URL)
        );
        $buildContext->initializeRuntimeConfig($rstContentsDir, $htmlOutputDir, $publicImagesDir, $publicImagesPrefix, $rstBookSubDir, !$this->useBuildCache, $this->theme);

        $configFileParser = new ConfigFileParser($buildContext, new NullOutput());
        $configFileParser->processConfigFile($rstContentsDir);

        $builder = new Builder(KernelFactory::createKernel($buildContext));
        $builder->build($buildContext->getSourceDir(), $buildContext->getOutputDir());

        $buildResult = new BuildResult($builder->getErrorManager()->getErrors());

        $missingFilesChecker = new MissingFilesChecker($buildContext);
        $missingFiles = $missingFilesChecker->getMissingFiles();
        foreach ($missingFiles as $missingFile) {
            $buildResult->appendError(sprintf('Missing file "%s"', $missingFile));
        }

        if (!$buildResult->isSuccessful()) {
            $buildResult->prependError(sprintf('Build errors from "%s"', date('Y-m-d h:i:s')));
            $filesystem->dumpFile($htmlOutputDir.'/build_errors.txt', implode("\n", $buildResult->getErrors()));
        }

        $metas = $builder->getMetas();
        if ($buildContext->getParseSubPath()) {
            $htmlForPdfGenerator = new HtmlForPdfGenerator($metas, $buildContext);
            $htmlForPdfGenerator->generateHtmlForPdf();
        } else {
            $jsonGenerator = new JsonGenerator($metas, $buildContext);
            $jsonGenerator->generateJson();
        }

        return $buildResult;
    }
}
