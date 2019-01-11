<?php

namespace SymfonyDocsBuilder\Code\Extractor;

class PhpCodeExtractor
{
    public function extractCode(string $source): CodeSource
    {
        $lines = explode("\n", $source);

        // make sure the first line is an opening PHP tag
        if (strpos($lines[0], '<?php') !== 0) {
            array_unshift($lines, '<?php');
        }

        if (strpos($lines[1], '// ') !== 0) {
            throw new \Exception(sprintf(sprintf('Could not find filename in code "%s"', $source)));
        }

        // skip "// "
        $filename = substr($lines[1], 3);

        return new CodeSource($filename, implode("\n", $lines));
    }
}
