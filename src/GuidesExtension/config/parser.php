<?php

/*
 * This file is part of the Docs Builder package.
 *
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use phpDocumentor\Guides\RestructuredText\Parser\Productions\DirectiveContentRule;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()->autowire()

        ->load('SymfonyTools\\DocsBuilder\\GuidesExtension\\Directives\\', '../src/Directives')
            ->bind('$startingRule', service(DirectiveContentRule::class))
            ->tag('phpdoc.guides.directive')

        ->load('SymfonyTools\\DocsBuilder\\GuidesExtension\\TextRole\\', '../src/TextRole')
            ->tag('phpdoc.guides.parser.rst.text_role')
    ;
};
