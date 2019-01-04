<?php

// app/config/services.php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container) {
    $container = $container->services()->defaults()
        ->private()
        ->autoconfigure()
        ->autowire();

    $container
        ->load('SymfonyDocsBuilder\\', '../src/*');
};