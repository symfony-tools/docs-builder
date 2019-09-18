<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;

class GithubReleaseCommand extends Command
{
    protected static $defaultName = 'github:release';

    /** @var SymfonyStyle */
    private $io;

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this->setDescription('Create a release on github with a .phar as attachment.');
        // todo valid with regex
        $this->addArgument('tag', InputArgument::REQUIRED, 'Release\'s tag.');
        $this->addArgument('name', InputArgument::REQUIRED, 'Release name');
        $this->addArgument('description', InputArgument::REQUIRED, 'Release description');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $process = Process::fromShellCommandline('./bin/compile', __DIR__.'/../..');
        $process->mustRun();

        // TODO token form .env
        $client = HttpClient::create(
            [
                'headers' => [
                    'Authorization' => 'token 52a83ae437c06017d72fc9461392f02b39dc8c0f',
                ],
            ]
        );

        try {
            $client->request(
                'POST',
                'https://api.github.com/repos/nikophil/test/releases',
                [
                    'json' => [
                        'tag_name' => $input->getArgument('tag'),
                        'target_commitish' => 'master',
                        'name' => $input->getArgument('name'),
                        'body' => $input->getArgument('description'),
                        'draft' => false,
                        'prerelease' => false
                    ]
                ]
            );
        } catch (ClientException $exception) {
            $this->io->error('Error while trying to create release. Maybe the tag name already exists?');

            return;
        }
    }
}
