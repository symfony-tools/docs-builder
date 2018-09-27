<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TocNode as Base;
use function is_array;

class TocNode extends Base
{
    /** @var int */
    private $depth;

    public function render() : string
    {
        if (isset($this->options['hidden'])) {
            return '';
        }

        $this->depth = (int) ($this->options['maxdepth'] ?? -1);

        $html = '<div class="toctree-wrapper compound"><ul>';

        foreach ($this->files as $file) {
            $reference = $this->environment->resolve('doc', $file);

            $url = $this->environment->relativeUrl($reference->getUrl());

            $html .= $this->renderLevel($url, $reference->getTitles());
        }

        $html .= '</ul></div>';

        return $html;
    }

    /**
     * @param mixed[]|array $titles
     * @param mixed[]       $path
     */
    private function renderLevel(
        ?string $url,
        array $titles,
        int $level = 1,
        array $path = []
    ) : string {

        if (-1 !== $this->depth && $level > $this->depth) {
            return '';
        }

        $html = '';
        foreach ($titles as $k => $entry) {
            $path[$level - 1] = (int) $k + 1;

            list($title, $childs) = $entry;

            if (is_array($title)) {
                list($title, $target) = $title;

                $info = $this->environment->resolve('doc', $target);

                $url = $this->environment->relativeUrl($info->getUrl());
            }

            $html .= sprintf('<li class="toctree-l%s"><a class="reference internal" href="%s">%s</a></li>', $level, $url, $title);

            if (! $childs) {
                continue;
            }

            $html .= '<ul>';
            $html .= $this->renderLevel($url, $childs, $level + 1, $path);
            $html .= '</ul>';
        }

        return $html;
    }
}
