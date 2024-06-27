<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
class RssFeedService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * Retrieve links from an RSS feed.
     *
     * @param string $url
     * @return array
     */
    public function getLinksFromRssFeed(string $url): array
    {
        try {
            $response = $this->httpClient->request('GET', $url);
            $content = $response->getContent();
            $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $this->logger->error('An error occurred while parsing the RSS feed: ' . $e->getMessage(), ['exception' => $e]);
            return [];
        } catch (\Exception $e) {
            $this->logger->error('An unexpected error occurred while parsing the RSS feed: ' . $e->getMessage(), ['exception' => $e]);
            return [];
        }

        $links = [];
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $content = (string) $item->children('content', true);
                if ($this->containsImage($content)) {
                    $links[] = (string) $item->link;
                }
            }
        }

        return $links;
    }

    /**
     * Check if the content contains an image.
     *
     * @param string $content
     * @return bool
     */
    private function containsImage(string $content): bool
    {
        return preg_match('/<img[^>]+src="([^">]+)"/', $content);
    }
}