<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\References\Resolver;

class DocReference extends Reference
{
    /** @var string */
    private $name;

    /** @var Resolver */
    private $resolver;

    public function __construct(string $name = 'doc')
    {
        $this->name     = $name;
        $this->resolver = new Resolver();
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function resolve(Environment $environment, string $data) : ResolvedReference
    {
        return $this->resolver->resolve($environment, $data, ['class' => 'reference internal', 'domElement' => 'em']);
    }

    public function found(Environment $environment, string $data) : void
    {
        $environment->addDependency($data);
    }
}
