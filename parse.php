<?php

require __DIR__.'/vendor/autoload.php';

use Doctrine\RST\Builder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocs\HtmlKernel;
use SymfonyDocs\JsonGenerator;

$kernel  = new HtmlKernel();
$builder = new Builder($kernel);

$fs = new Filesystem();
$fs->remove($htmlOutputDir = __DIR__.'/html');
$fs->remove($jsonOutputDir = __DIR__.'/json');

$builder->build(
    __DIR__.'/..',
    $htmlOutputDir
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

$jsonGenerator = new JsonGenerator($builder->getDocuments()->getAll());
$jsonGenerator->generateJson($htmlOutputDir, $jsonOutputDir);


