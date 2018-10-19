<?php

require __DIR__.'/vendor/autoload.php';

use Doctrine\RST\Builder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocs\HtmlKernel;

$kernel  = new HtmlKernel();
$builder = new Builder($kernel);

$fs = new Filesystem();
$fs->remove(__DIR__.'/html');

$builder->build(
    __DIR__.'/..',
    __DIR__.'/html',
    true
);

$finder = new Finder();
$finder->in(__DIR__.'/..')
    ->exclude(['_build', '.github', '.platform', '_images'])
    ->notName('*.rst.inc')
    ->name('*.rst');

foreach ($finder as $file) {
    $htmlFile = str_replace(['/home/niko/works/symfony-docs', '.rst'], ['/home/niko/works/symfony-docs/_build/html', '.html'], $file->getRealPath());
    if (!file_exists($htmlFile)) {
        dump("missing file: ".$htmlFile);
    }
}


