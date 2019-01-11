<?php

namespace SymfonyDocsBuilder\Code\Extractor;

class YamlCodeExtractor
{
    public function extractCode(string $source): CodeSource
    {
        $lines = explode("\n", $source);

        if (strpos($lines[0], '# ') !== 0) {
            throw new \Exception(sprintf(sprintf('Could not find filename in code "%s"', $source)));
        }

        // skip "# "
        $filename = substr($lines[0], 2);

        return new CodeSource($filename, implode("\n", $lines));
    }
}
