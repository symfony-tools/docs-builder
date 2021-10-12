<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Listener;

use Doctrine\RST\Event\PreParseDocumentEvent;

final class AdmonitionListener
{
    public function preParseDocument(PreParseDocumentEvent $event)
    {
        // TODO: remove this temporary fix when Symfony Docs are updated to use the new '.. screencast::' directive
        $event->setContents(str_replace('.. admonition:: Screencast', '.. screencast::', $event->getContents()));
    }
}
