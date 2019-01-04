<?php declare(strict_types=1);

namespace SymfonyDocsBuilder;

use Doctrine\RST\Configuration as RSTParserConfiguration;
use Doctrine\RST\Event\PostBuildRenderEvent;
use Doctrine\RST\Kernel;
use SymfonyDocsBuilder\Directive as SymfonyDirectives;
use SymfonyDocsBuilder\Listener\CopyImagesDirectoryListener;
use SymfonyDocsBuilder\Reference as SymfonyReferences;

/**
 * Class KernelFactory
 */
final class KernelFactory
{
    private $parameterBag;
    private $copyImagesDirectoryListener;
    private $symfonyDocUrl;
    private $symfonyApiUrl;
    private $phpDocUrl;
    private $basePath;

    public function __construct(
        ParameterBag $parameterBag,
        CopyImagesDirectoryListener $copyImagesDirectoryListener,
        string $symfonyDocUrl,
        string $symfonyApiUrl,
        string $phpDocUrl,
        string $basePath
    ) {
        $this->parameterBag                = $parameterBag;
        $this->copyImagesDirectoryListener = $copyImagesDirectoryListener;

        $this->symfonyDocUrl = $symfonyDocUrl;
        $this->symfonyApiUrl = $symfonyApiUrl;
        $this->phpDocUrl     = $phpDocUrl;
        $this->basePath      = $basePath;
    }

    public function createKernel(): Kernel
    {
        $configuration = new RSTParserConfiguration();
        $configuration->setCustomTemplateDirs([sprintf('%s/src/Templates', $this->basePath)]);
        $configuration->setCacheDir(sprintf('%s/var/cache', $this->basePath));
        $configuration->addFormat(
            new SymfonyHTMLFormat(
                $configuration->getTemplateRenderer(),
                $configuration->getFormat()
            )
        );

        if ($parseOnlyPath = $this->parameterBag->get('parseOnly')) {
            $configuration->setBaseUrl($this->symfonyDocUrl);
            $configuration->setBaseUrlEnabledCallable(
                static function (string $path) use ($parseOnlyPath) : bool {
                    return strpos($path, $parseOnlyPath) !== 0;
                }
            );
        }

        $configuration->getEventManager()->addEventListener(
            PostBuildRenderEvent::POST_BUILD_RENDER,
            $this->copyImagesDirectoryListener
        );

        return new Kernel(
            $configuration,
            $this->getDirectives(),
            $this->getReferences()
        );
    }

    private function getDirectives(): array
    {
        return [
            new SymfonyDirectives\AdmonitionDirective(),
            new SymfonyDirectives\CautionDirective(),
            new SymfonyDirectives\CodeBlockDirective(),
            new SymfonyDirectives\ConfigurationBlockDirective(),
            new SymfonyDirectives\IndexDirective(),
            new SymfonyDirectives\RoleDirective(),
            new SymfonyDirectives\NoteDirective(),
            new SymfonyDirectives\SeeAlsoDirective(),
            new SymfonyDirectives\SidebarDirective(),
            new SymfonyDirectives\TipDirective(),
            new SymfonyDirectives\TopicDirective(),
            new SymfonyDirectives\WarningDirective(),
            new SymfonyDirectives\VersionAddedDirective(),
            new SymfonyDirectives\BestPracticeDirective(),
        ];
    }

    private function getReferences(): array
    {
        return [
            new SymfonyReferences\ClassReference($this->symfonyApiUrl),
            new SymfonyReferences\MethodReference($this->symfonyApiUrl),
            new SymfonyReferences\NamespaceReference($this->symfonyApiUrl),
            new SymfonyReferences\PhpFunctionReference($this->phpDocUrl),
            new SymfonyReferences\PhpMethodReference($this->phpDocUrl),
            new SymfonyReferences\PhpClassReference($this->phpDocUrl),
        ];
    }
}
