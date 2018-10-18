<?php

namespace SymfonyDocs\Nodes;

trait ClassTrait
{
    /** @var string */
    private $class;

    /**
     * @return string
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass(?string $class)
    {
        $this->class = $class;
    }
}
