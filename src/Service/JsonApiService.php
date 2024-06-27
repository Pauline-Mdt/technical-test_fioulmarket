<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JsonApiService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * Retrieve links from a JSON API.
     *
     * @param string $url
     * @param string|null $bearerToken
     * @return array
     */
    public function getLinksFromJsonApi(string $url, string $bearerToken = null): array
    {
        $options = [];
        if ($bearerToken) {
            $options['headers'] = [
                'Authorization' => 'Bearer ' . $bearerToken
            ];
        }

        try {
            $response = $this->httpClient->request('GET', $url, $options);
            $content = $response->getContent();
            $json = json_decode($content);
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $this->logger->error('An error occurred while parsing the RSS feed: ' . $e->getMessage(), ['exception' => $e]);
            return [];
        } catch (\Exception $e) {
            $this->logger->error('An unexpected error occurred while parsing the RSS feed: ' . $e->getMessage(), ['exception' => $e]);
            return [];
        }

        $links = [];
        if (isset($json->articles)) {
            foreach ($json->articles as $article) {
                if (!empty($article->urlToImage)) {
                    $links[] = $article->url;
                }
            }
        }

        return $links;
    }
}