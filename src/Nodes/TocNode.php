<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TocNode as Base;
use function is_array;

class TocNode extends Base
{
    /** @var int */
    private $depth;

    public function doRender(): string
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

    private function renderLevel(?string $url, array $titles, int $level = 1, array $path = []): string
    {
        if (-1 !== $this->depth && $level > $this->depth) {
            return '';
        }

        $html = '';
        foreach ($titles as $k => $entry) {
            $path[$level - 1] = (int) $k + 1;

            [$title, $children] = $entry;

            $slug = $title;

            if (is_array($title)) {
                $slug = $title[1];
            }

            $anchor = Environment::slugify($slug);
            $target = $url;
            if (1 !== $level) {
                $target = $url.'#'.$anchor;
            }

            if (is_array($title)) {
                [$title, $target] = $title;

                $info = $this->environment->resolve('doc', $target);

                $target = $this->environment->relativeUrl($info->getUrl());
            }

            $html .= sprintf('<li class="toctree-l%s"><a class="reference internal" href="%s">%s</a>', $level, $target, $title);

            if ($children && ($level < $this->depth || -1 === $this->depth)) {
                $html .= '<ul>';
                $html .= $this->renderLevel($url, $children, $level + 1, $path);
                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        return $html;
    }
}
