<?php declare(strict_types=1);

namespace SymfonyDocsBuilder;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use SymfonyDocsBuilder\Command\BuildDocsCommand;

class Application
{
    private $application;
    private $buildDocsCommand;

    public function __construct(BaseApplication $application, BuildDocsCommand $buildDocsCommand)
    {
        $this->application      = $application;
        $this->buildDocsCommand = $buildDocsCommand;
    }

    public function run(InputInterface $input): int
    {
        $inputOption = new InputOption(
            'symfony-version',
            null,
            InputOption::VALUE_REQUIRED,
            'The symfony version of the doc to parse.',
            false === getenv('SYMFONY_VERSION') ? '4.1' : getenv('SYMFONY_VERSION')
        );
        $this->application->getDefinition()->addOption($inputOption);
        $this->application->add($this->buildDocsCommand);

        return $this->application->run($input);
    }

    public static function createContainer(string $version): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('symfony_docs_builder.base_path', realpath(__DIR__.'/..'));
        $container->setParameter('symfony_docs_builder.symfony_version', $version);

        $configuration = self::getSymfonyDocConfiguration($container->getParameter('symfony_docs_builder.base_path'));
        $container->setParameter('symfony_docs_builder.symfony_api_url', sprintf($configuration['symfony_api_url'], $version));
        $container->setParameter('symfony_docs_builder.php_doc_url', $configuration['php_doc_url']);
        $container->setParameter('symfony_docs_builder.symfony_doc_url', sprintf($configuration['symfony_doc_url'], $version));

        $loader = new PhpFileLoader($container, new FileLocator(sprintf('%s/config', $container->getParameter('symfony_docs_builder.base_path'))));
        $loader->load('services.php');

        $container->compile();

        return $container;
    }

    private static function getSymfonyDocConfiguration(string $basePath): array
    {
        return json_decode(file_get_contents(sprintf('%s/conf.json', $basePath)), true);
    }
}