<?php

namespace SymfonyDocsBuilder\TextRole;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;
use function Symfony\Component\String\u;

class MethodRole extends ExternalLinkRole
{
    public function getName(): string
    {
        return 'method';
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        $data = u($spanToken->get('text'));
        if (!$data->containsAny('::')) {
            throw new \RuntimeException(sprintf('Malformed method reference "%s" in file "%s"', $data, $environment->getCurrentFileName()));
        }

        [$className, $methodName] = $data->split('::', 2);
        $className = $className->replace('\\\\', '\\');

        return $this->renderLink(
            $environment,
            $methodName.'()',
            sprintf('%s/%s.php#method_%s', $this->baseUrl, $className->replace('\\', '/'), $methodName),
            sprintf('%s::%s()', $className, $methodName)
        );
    }
}
