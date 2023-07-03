<?php

namespace SymfonyTools\GuidesExtension\Directives;

use Psr\Log\LoggerInterface;
use SymfonyTools\GuidesExtension\Node\ConfigurationBlockNode;
use SymfonyTools\GuidesExtension\Node\ConfigurationTab;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

class ConfigurationBlockDirective extends SubDirective
{
    private const LANGUAGE_LABELS = [
        'caddy' => 'Caddy',
        'env' => 'Bash',
        'html+jinja' => 'Twig',
        'html+php' => 'PHP',
        'html+twig' => 'Twig',
        'jinja' => 'Twig',
        'php' => 'PHP',
        'php-annotations' => 'Annotations',
        'php-attributes' => 'Attributes',
        'php-standalone' => 'Standalone Use',
        'php-symfony' => 'Framework Use',
        'rst' => 'RST',
        'terminal' => 'Bash',
        'varnish3' => 'Varnish 3',
        'varnish4' => 'Varnish 4',
        'vcl' => 'VCL',
        'xml' => 'XML',
        'xml+php' => 'XML',
        'yaml' => 'YAML',
    ];

    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function getName(): string
    {
        return 'configuration-block';
    }

    protected function processSub(DocumentNode $document, Directive $directive): ?Node
    {
        $tabs = [];
        foreach ($document->getValue() as $child) {
            if (!$child instanceof CodeNode) {
                $this->logger->warning('The ".. configuration-block::" directive only supports code blocks, "'.get_debug_type($child).'" given in "'.$document->getFilePath().'".');

                continue;
            }

            $label = self::LANGUAGE_LABELS[$child->getLanguage()] ?? ucfirst(str_replace('-', ' ', $child->getLanguage()));

            $tabs[] = new ConfigurationTab($label, $child);
        }

        return new ConfigurationBlockNode($tabs);
    }
}
