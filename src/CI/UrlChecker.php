<?php declare(strict_types=1);

namespace SymfonyDocs\CI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class UrlChecker
{
    private $invalidUrls = [];

    public function checkUrl(string $url)
    {
        $httpClient = new Client(['timeout' => 10]);

        try {
            $response   = $httpClient->get($url, ['http_errors' => false]);
            $statusCode = $response->getStatusCode();
        } catch (GuzzleException $exception) {
            $statusCode = 0;
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $this->invalidUrls[] = [
                'url'        => $url,
                'statusCode' => $statusCode,
            ];
        }
    }

    public function getInvalidUrls(): array
    {
        return $this->invalidUrls;
    }
}
