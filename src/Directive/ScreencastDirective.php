<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Directive;

class ScreencastDirective extends AbstractAdmonitionDirective
{
    public function __construct()
    {
        parent::__construct('screencast', 'Screencast');
    }
}
