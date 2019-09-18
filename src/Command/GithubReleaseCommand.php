<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubReleaseCommand extends Command
{
    // todo change repo
    private const GITHUB_USER = 'nikophil';
    private const GITHUB_REPO = 'test';

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
        $this->addArgument('tag', InputArgument::REQUIRED, 'Release\'s tag.');
        $this->addArgument('name', InputArgument::OPTIONAL, 'Release name', 'Symfony docs builder %s');
        $this->addArgument('description', InputArgument::OPTIONAL, 'Symfony docs builder %s');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        if (empty($_ENV['GITHUB_API_TOKEN'])) {
            throw new RuntimeException('Please fill "GITHUB_API_TOKEN" in file "[PROJECT_DIR]/.env"');
        }

        $this->io     = new SymfonyStyle($input, $output);
        $this->client = $this->createHttpClient($_ENV['GITHUB_API_TOKEN']);

        $tag = $input->getArgument('tag');
        if (!preg_match('/^v\d+\.\d+\.\d+$/', $tag)) {
            throw new RuntimeException(sprintf('"%s" is not a valid tag.', $tag));
        }

        $this->tag         = $tag;
        $this->name        = sprintf($input->getArgument('name'), $tag);
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

    private function createHttpClient(string $githubToken): HttpClientInterface
    {
        $client = HttpClient::create(
            [
                'headers' => [
                    'Authorization' => sprintf('token %s', $githubToken),
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
                sprintf('https://api.github.com/repos/%s/%s/releases', self::GITHUB_USER, self::GITHUB_REPO),
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
            if (401 === $exception->getCode()) {
                $message = 'Invalid token';
            } else {
                $message = 'Maybe the tag name already exists?';
            }

            throw new RuntimeException(sprintf('Error while trying to create release: %s.', $message), 0, $exception);
        }
    }

    private function addAssetToRelease(int $releaseId): void
    {
        try {
            $this->client->request(
                'POST',
                sprintf(
                    'https://uploads.github.com/repos/%s/%s/releases/%s/assets?name=docs.phar',
                    self::GITHUB_USER,
                    self::GITHUB_REPO,
                    $releaseId
                ),
                [
                    'headers' => ['Content-Type' => 'application/octet-stream'],
                    'body'    => file_get_contents(__DIR__.'/../../docs.phar'),
                ]
            );
        } catch (ClientException $exception) {
            $this->deleteRelease($releaseId);
            throw new RuntimeException('Error while adding asset to release.', 0, $exception);
        }
    }

    private function deleteRelease(int $releaseId): void
    {
        try {
            $this->client->request(
                'DELETE',
                sprintf('https://api.github.com/repos/%s/%s/releases/%s', self::GITHUB_USER, self::GITHUB_REPO, $releaseId)
            );
        } catch (ClientException $exception) {
            throw new RuntimeException('Error while deleting release.', 0, $exception);
        }
    }
}
