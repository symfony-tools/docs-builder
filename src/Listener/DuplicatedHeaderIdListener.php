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
use SymfonyDocsBuilder\Renderers\TitleNodeRenderer;

final class DuplicatedHeaderIdListener
{
    public function preParseDocument(PreParseDocumentEvent $event): void
    {
        // needed because we only need to handle duplicated headers within
        // the same file, not across all the files being generated
        TitleNodeRenderer::resetHeaderIdCache();
    }
}
