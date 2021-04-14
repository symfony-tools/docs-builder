<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Directive;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use function strtoupper;

class ConfigurationBlockDirective extends SubDirective
{
    private const LANGUAGE_LABELS = [
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

    public function getName(): string
    {
        return 'configuration-block';
    }

    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        $blocks = [];
        foreach ($document->getNodes() as $node) {
            if (!$node instanceof CodeNode) {
                continue;
            }

            $language = $node->getLanguage() ?? 'Unknown';

            $blocks[] = [
                'language_label' => self::LANGUAGE_LABELS[$language] ?? ucfirst(str_replace('-', ' ', $language)),
                'language' => $language,
                'code' => $node->render(),
            ];
        }

        $wrapperDiv = $parser->renderTemplate(
            'directives/configuration-block.html.twig',
            [
                'blocks' => $blocks,
            ]
        );

        return $parser->getNodeFactory()->createWrapperNode(null, $wrapperDiv, '</div>');
    }
}
