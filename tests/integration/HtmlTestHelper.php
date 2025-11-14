<?php

namespace SymfonyTools\GuidesExtension\Tests;

trait HtmlTestHelper
{
    static private function sanitizeHTML(string $html): string
    {
        $html = implode("\n", array_map('trim', explode("\n", $html)));
        $html = preg_replace('# +#', ' ', $html);
        $html = preg_replace('#(?<!\w) <#', '<', $html);
        $html = preg_replace('#> (?!\w)#', '>', $html);
        $html = preg_replace('#\R+#', "\n", $html);

        $html = substr($html, strpos($html, '<body>') + 6);
        $html = substr($html, 0, strpos($html, '</body>'));

        return trim($html);
    }
}
