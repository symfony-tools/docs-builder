<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubReleaseCommand extends Command
{
    protected static $defaultName = 'github:release';

    /** @var SymfonyStyle */
    private $io;
    /** @var HttpClientInterface */
    private $client;

    private $tag;
    private $name;
    private $description;

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this->setDescription('Create a release on github with a .phar as attachment.');
        // todo valid with regex
        $this->addArgument('tag', InputArgument::REQUIRED, 'Release\'s tag.');
        $this->addArgument('name', InputArgument::OPTIONAL, 'Release name', 'Symfony docs builder %s');
        $this->addArgument('description', InputArgument::OPTIONAL, 'Symfony docs builder %s');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->client = $this->createHttpClient();

        $tag = $input->getArgument('tag');
        if (!preg_match('/v\d+\.\d+\.\d+/', $tag)) {
            throw new RuntimeException(sprintf('"%s" is not a valid tag.', $tag));
        }

        $this->tag = $tag;
        $this->name = sprintf($input->getArgument('name'), $tag);
        $this->description = sprintf($input->getArgument('description'), $tag);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->compilePhar();

        $this->addAssetToRelease($this->createRelease());
    }

    /**
     * @throws ProcessFailedException
     */
    private function compilePhar(): void
    {
        $process = Process::fromShellCommandline('./bin/compile', __DIR__.'/../..');
        $process->mustRun();
    }

    private function createHttpClient(): HttpClientInterface
    {
// TODO token form .env
        $client = HttpClient::create(
            [
                'headers' => [
                    'Authorization' => 'token 52a83ae437c06017d72fc9461392f02b39dc8c0f',
                ],
            ]
        );

        return $client;
    }

    private function createRelease(): int
    {
        try {
            $response = $this->client->request(
                'POST',
                // todo change repo
                'https://api.github.com/repos/nikophil/test/releases',
                [
                    'json' => [
                        'tag_name'         => $this->tag,
                        'target_commitish' => 'master',
                        'name'             => $this->name,
                        'description'      => $this->description,
                        'draft'            => false,
                        'prerelease'       => false,
                    ],
                ]
            );

            return (int) $response->toArray()['id'];
        } catch (ClientException $exception) {
            throw new RuntimeException('Error while trying to create release. Maybe the tag name already exists?', 0, $exception);
        }
    }

    private function addAssetToRelease(int $releaseId): void
    {
        try {
            $this->client->request(
                'POST',
                sprintf('https://uploads.github.com/repos/nikophil/test/releases/%s/assets?name=docs.phar', $releaseId),
                [
                    'headers' => ['Content-Type' => 'application/octet-stream'],
                    'body'    => file_get_contents(__DIR__.'/../../docs.phar'),
                ]
            );
        } catch (ClientException $exception) {
            $this->deleteRelease($releaseId);
            throw new RuntimeException('Error while adding asset to release. Maybe the tag name already exists?', 0, $exception);
        }
    }

    private function deleteRelease(int $releaseId): void
    {
        try {
            $this->client->request(
                'DELETE',
                sprintf('https://api.github.com/repos/nikophil/test/releases/%s', $releaseId)
            );
        } catch (ClientException $exception) {
            throw new RuntimeException('Error while deleting release.', 0, $exception);
        }
    }
}
