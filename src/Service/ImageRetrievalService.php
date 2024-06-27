<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class ImageRetrievalService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Retrieve images from a list of URLs.
     *
     * @param array $urls
     * @return array
     */
    public function getImagesFromUrls(array $urls): array
    {
        $images = [];
        foreach ($urls as $url) {
            $image = $this->getImageFromUrl($url);
            if ($image) {
                $images[] = $image;
            }
        }

        return $images;
    }

    /**
     * Retrieve an image from a given URL.
     *
     * @param string $url
     * @return string|null
     */
    public function getImageFromUrl(string $url): ?string
    {
        try {
            $doc = new \DOMDocument();
            @$doc->loadHTMLFile($url);
            $xpath = new \DOMXPath($doc);
        } catch (\Exception $e) {
            $this->logger->error('An error occurred while parsing the HTML content: ' . $e->getMessage(), ['exception' => $e]);
            return null;
        }

        if (str_contains($url, 'commitstrip.com')) {
            $src = $xpath->evaluate("string(//img[contains(@class, 'size-full')]/@src)");
        } else {
            $src = $xpath->evaluate("string(//img/@src)");
        }

        if ($this->isValidImage($src)) {
            return $src;
        }

        return null;
    }

    /**
     * Check if the given URL points to a valid image.
     *
     * @param string $url
     * @return bool
     */
    private function isValidImage(string $url): bool
    {
        // Check if the URL is a base64 encoded image
        if (preg_match('/^data:image\/(\w+);base64,/', $url)) {
            return true;
        }

        // Check if the URL is valid
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        // Fetch the image content
        $imageContent = file_get_contents($url);
        if ($imageContent === false) {
            return false;
        }

        // Check if the image content is valid
        if (getimagesizefromstring($imageContent) === false) {
            return false;
        }
        $image = imagecreatefromstring($imageContent);

        // Optional: Check dimensions to exclude tiny images
        $width = imagesx($image);
        $height = imagesy($image);
        if ($width < 100 || $height < 100) {
            return false;
        }

        imagedestroy($image);
        return true;
    }
}