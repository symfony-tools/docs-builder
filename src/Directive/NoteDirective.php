<?php

namespace SymfonyDocs\Directive;

class NoteDirective extends AbstractAdmonitionDirective
{
    public function __construct()
    {
        parent::__construct('note', 'Note');
    }
}
