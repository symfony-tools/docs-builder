<?php

require __DIR__.'/vendor/autoload.php';

use Doctrine\RST\Builder;
use SymfonyDocs\HtmlKernel;

$kernel = new HtmlKernel();
$builder = new Builder($kernel);

$builder->build(
    __DIR__.'/..',
    __DIR__.'/html',
    false
);
