<?php

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use SymfonyDocsBuilder\Application;

return function (ContainerConfigurator $container) {
    $container = $container->services()->defaults()
        ->private()
        ->autoconfigure()
        ->autowire()
        ->bind('$basePath', '%symfony_docs_builder.base_path%')
//        ->bind('$symfonyVersion', '%symfony_docs_builder.symfony_version%')
        ->bind('$symfonyApiUrl', '%symfony_docs_builder.symfony_api_url%')
        ->bind('$phpDocUrl', '%symfony_docs_builder.php_doc_url%')
        ->bind('$symfonyDocUrl', '%symfony_docs_builder.symfony_doc_url%');

    $container
        ->load('SymfonyDocsBuilder\\', '../src/*')
        ->exclude('../src/{Renderers,Reference,Directive,SymfonyHTMLFormat.php}');

    $container->set(BaseApplication::class)
        ->private()
        ->autowire();

    $container->set(ParameterBag::class)
        ->private()
        ->autowire();

    $container->set(Application::class)
        ->public()
        ->autowire()
        ->autoconfigure();
};