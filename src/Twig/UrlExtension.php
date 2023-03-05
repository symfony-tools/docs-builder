<?php

namespace SymfonyDocsBuilder\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;
use function Symfony\Component\String\u;

class UrlExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            new TwigTest('safe_url', [$this, 'isSafeUrl']),
        ];
    }

    /*
     * If the URL is considered safe, it's opened in the same browser tab;
     * otherwise it's opened in a new tab and with some strict security options.
     */
    public function isSafeUrl(string $url): bool
    {
        // The following are considered Symfony URLs:
        //   * https://symfony.com/[...]
        //   * https://[...].symfony.com/ (e.g. insight.symfony.com, etc.)
        //   * https://symfony.wip/[...]  (used for internal/local development)
        $url = u($url);
        $isSymfonyUrl = $url->match('{^http(s)?://(.*\.)?symfony.(com|wip)}');
        $isRelativeUrl = !$url->startsWith('http://') && !$url->startsWith('https://');

        return $isSymfonyUrl || $isRelativeUrl;
    }
}
