<?php

namespace App\Controller;

use App\Service\ImageRetrievalService;
use App\Service\JsonApiService;
use App\Service\RssFeedService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private RssFeedService $rssFeedService;
    private JsonApiService $jsonApiService;
    private ImageRetrievalService $imageRetrievalService;

    public function __construct(RssFeedService $rssFeedService, JsonApiService $jsonApiService, ImageRetrievalService $imageRetrievalService)
    {
        $this->rssFeedService = $rssFeedService;
        $this->jsonApiService = $jsonApiService;
        $this->imageRetrievalService = $imageRetrievalService;
    }

    /**
     * @Route("/", name="homepage")
     * @return Response
     */
    public function index(): Response
    {
        $commitStripLinks = $this->rssFeedService->getLinksFromRssFeed('http://www.commitstrip.com/en/feed/');
        $newsApiKey = $this->getParameter('NEWS_API_KEY');
        $newsApiLinks = $this->jsonApiService->getLinksFromJsonApi('https://newsapi.org/v2/top-headlines?country=us&apiKey=' . $newsApiKey);

        $allLinks = array_merge($commitStripLinks, $newsApiLinks);
        $uniqueLinks = array_keys(array_flip($allLinks));

        $images = $this->imageRetrievalService->getImagesFromUrls($uniqueLinks);

        return $this->render('default/index.html.twig', ['images' => $images]);
    }
}