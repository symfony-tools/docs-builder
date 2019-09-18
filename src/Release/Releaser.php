<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Release;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use SymfonyDocsBuilder\Phar\Compiler;

class Releaser
{
    // todo change repo
    private const GITHUB_USER = 'nikophil';
    private const GITHUB_REPO = 'test';

    /** @var HttpClientInterface */
    private $client;
    private $compiler;

    public function __construct(HttpClientInterface $client, Compiler $compiler)
    {
        $this->client   = $client;
        $this->compiler = $compiler;
    }

    public function createRelease(string $tag, string $name = 'Symfony docs builder %s', string $description = 'Symfony docs builder %s')
    {
        if (!preg_match('/^v\d+\.\d+\.\d+$/', $tag)) {
            throw new \RuntimeException(sprintf('"%s" is not a valid tag.', $tag));
        }

        $this->compiler->compile();

        $this->addAssetToRelease($releaseId = $this->createDraftRelease($tag, $name, $description));

        $this->publishRelease($releaseId);
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
        } catch (\RuntimeException $exception) {
            if (401 === $exception->getCode()) {
                $message = 'Error while trying to create release: Invalid token.';
            } else {
                $message = 'Error while trying to create release.';
            }

            // todo: create new exception which can be exploited in ./bin/create_release
            throw new \RuntimeException($message, 0, $exception);
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
        } catch (\RuntimeException $exception) {
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
        } catch (\RuntimeException $exception) {
            $this->deleteRelease($releaseId);
            throw new \RuntimeException('Error while publishing release. Maybe the tag name already exists?', 0, $exception);
        }
    }

    private function deleteRelease(int $releaseId): void
    {
        try {
            $this->client->request(
                'DELETE',
                sprintf('https://api.github.com/repos/%s/%s/releases/%s', self::GITHUB_USER, self::GITHUB_REPO, $releaseId)
            );
        } catch (\RuntimeException $exception) {
            throw new \RuntimeException('Error while deleting release.', 0, $exception);
        }
    }
}
