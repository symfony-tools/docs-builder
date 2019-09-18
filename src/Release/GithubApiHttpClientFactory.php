<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Release;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubApiHttpClientFactory
{
    private $githubApiToken;

    public function __construct()
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        if (empty($_SERVER['GITHUB_API_TOKEN'])) {
            throw new \RuntimeException('Please fill "GITHUB_API_TOKEN" in file "[PROJECT_DIR]/.env"');
        }

        $this->githubApiToken = $_SERVER['GITHUB_API_TOKEN'];
    }

    public function createHttpClient(): HttpClientInterface
    {
        $client = HttpClient::create(
            [
                'headers' => [
                    'Authorization' => sprintf('token %s', $this->githubApiToken),
                ],
            ]
        );

        return $client;
    }
}
