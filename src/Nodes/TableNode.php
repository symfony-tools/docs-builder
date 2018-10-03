<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\HTML\Span;
use Doctrine\RST\Nodes\TableNode as Base;

class TableNode extends Base
{
    public function render(): string
    {
        $html = '<table class="docutils" border="1">';

        $i = 0;
        if (0 !== \count($this->headers)) {
            $html .= '<thead valign="bottom"><tr class="row-odd">';

            foreach ($this->headers as $k => $isHeader) {
                if (isset($this->data[$k])) {
                    /** @var Span $col */
                    foreach ($this->data[$k] as &$col) {
                        $html .= sprintf('<th class="head">%s</th>', $col->render());
                    }

                    unset($this->data[$k]);
                }
            }

            $html .= '</tr></thead>';
            $i++;
        }

        $html .= '<tbody valign="top">';

        foreach ($this->data as $k => &$row) {
            if ($row === []) {
                continue;
            }

            $html .= sprintf('<tr class="row-%s">', $i++ % 2 ? 'even' : 'odd');

            /** @var Span $col */
            foreach ($row as &$col) {
                $html .= sprintf('<td>%s</td>', $col->render());
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}

