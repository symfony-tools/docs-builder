<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\DocsBuilder\GuidesExtension\UrlGenerator;

use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use SymfonyTools\DocsBuilder\GuidesExtension\Build\BuildConfig;

final class PathPrefixUrlGenerator implements UrlGeneratorInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private BuildConfig $buildConfig,
    ) {
    }

    public function createFileUrl(RenderContext $context, string $filename, ?string $anchor = null): string
    {
        return $this->urlGenerator->createFileUrl($context, $filename, $anchor);
    }

    public function generateCanonicalOutputUrl(RenderContext $context, string $reference, ?string $anchor = null): string
    {
        return $this->urlGenerator->generateCanonicalOutputUrl($context, $reference, $anchor);
    }

    public function generateInternalUrl(RenderContext $renderContext, string $canonicalUrl): string
    {
        return \sprintf('%s/%s', rtrim($this->buildConfig->assetsBaseUri, '/'), ltrim($canonicalUrl, '/'));
    }
}
