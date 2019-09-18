<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Release;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Releaser
{
    // todo change repo
    private const GITHUB_USER = 'nikophil';
    private const GITHUB_REPO = 'test';

    /** @var HttpClientInterface */
    private $client;

    public function __construct(GithubApiHttpClientFactory $githubApiHttpClientFactory)
    {
        $this->client = $githubApiHttpClientFactory->createHttpClient();
    }

    public function doRelease(string $tag, string $name = 'Symfony docs builder %s', string $description = 'Symfony docs builder %s')
    {
        if (!preg_match('/^v\d+\.\d+\.\d+$/', $tag)) {
            throw new \RuntimeException(sprintf('"%s" is not a valid tag.', $tag));
        }

        $this->compilePhar();

        $this->addAssetToRelease($releaseId = $this->createDraftRelease($tag, $name, $description));

        $this->publishRelease($releaseId);
    }

    /**
     * @throws ProcessFailedException
     */
    private function compilePhar(): void
    {
        $process = Process::fromShellCommandline('./bin/compile', __DIR__.'/../..');
        $process->mustRun();
    }

    private function createDraftRelease(string $tag, string $name, string $description): int
    {
        try {
            $response = $this->client->request(
                'POST',
                sprintf('https://api.github.com/repos/%s/%s/releases', self::GITHUB_USER, self::GITHUB_REPO),
                [
                    'json' => [
                        'tag_name'         => $tag,
                        'target_commitish' => 'master',
                        'name'             => sprintf($name, $tag),
                        'description'      => sprintf($description, $tag),
                        'draft'            => true,
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

            throw new \RuntimeException(sprintf('Error while trying to create release: %s.', $message), 0, $exception);
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
            throw new \RuntimeException('Error while adding asset to release.', 0, $exception);
        }
    }

    private function publishRelease(int $releaseId): void
    {
        try {
            $this->client->request(
                'PATCH',
                sprintf('https://api.github.com/repos/%s/%s/releases/%s', self::GITHUB_USER, self::GITHUB_REPO, $releaseId),
                [
                    'json' => [
                        'draft' => false,
                    ],
                ]
            );
        } catch (ClientException $exception) {
            $this->deleteRelease($releaseId);
            throw new \RuntimeException('Error while publishing release.', 0, $exception);
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
            throw new \RuntimeException('Error while deleting release.', 0, $exception);
        }
    }
}
