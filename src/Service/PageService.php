<?php

namespace App\Service;

use App\Entity\MetaTag;
use App\Entity\Page;
use App\Repository\MetaTagRepository;
use App\Repository\PageRepository;
use Exception;
use mikehaertl\wkhtmlto\Image;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * PageService
 */
class PageService
{
    /**
     * regex for title
     */
    const TITLEREGEX = '/<title[^>]*>(.*?)<\/title>/ims';

    /**
     * @var PageRepository $pageRepository
     */
    private PageRepository $pageRepository;

    /**
     * @var MetaTagService
     */
    private MetaTagService $metaTagService;

    private MetaTagRepository $metaTagRepository;

    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * @var ContainerBagInterface $parameterBag
     */
    private ContainerBagInterface $parameterBag;

    /**
     * @param PageRepository $pageRepository
     * @param MetaTagService $metaTagService
     * @param MetaTagRepository $metaTagRepository
     * @param LoggerInterface $logger
     * @param ContainerBagInterface $parameterBag
     */
    public function __construct(
        PageRepository $pageRepository,
        MetaTagService $metaTagService,
        MetaTagRepository $metaTagRepository,
        LoggerInterface $logger,
        ContainerBagInterface $parameterBag
    ) {
        $this->pageRepository = $pageRepository;
        $this->metaTagService = $metaTagService;
        $this->metaTagRepository = $metaTagRepository;
        $this->logger = $logger;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param $url
     * @return bool
     */
    public function validUrl($url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $url
     * @return array
     * @throws Exception
     */
    public function validateUrlAndGetContent(string $url): array
    {
        $result = [
            'title' => null,
            'metaTags' => [],
            'description' => null,
        ];

        try {
            if (!$this->validUrl($url)) {
                $this->logger->error('Invalid URL format: ' . $url);
                return $result;
            }

            $context = stream_context_create([
                'http' => [
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                ],
            ]);

            $content = file_get_contents($url, false, $context);

            if ($content === false) {
                $this->logger->error('Failed to retrieve content from ' . $url);
                return $result;
            }

            $result['title'] = $this->extractPageTitle($content);
            $metaTags = array_change_key_case($this->metaTagService->getMetaTags($content), CASE_LOWER);
            $result['metaTags'] = $metaTags;
            $result['description'] = $metaTags['description'] ?? null;

            return $result;
        } catch (Exception $exception) {
            $this->logger->error('An error occurred in validateUrlAndGetContent.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ]);
            throw $exception;
        }
    }

    /**
     * @param string $url
     * @return Page|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function saveAndAnalyze(string $url): ?Page
    {
        try {
            $page = $this->pageRepository->findOneByUrl($url);
            if (empty($page)) {
                $pageContent = $this->validateUrlAndGetContent($url);
                $pageTitle = $pageContent['title'];
                $pageTags = $pageContent['metaTags'];
                $pageDescription = $pageContent['description'];

                // save Page
                $page = new Page();
                $page->setUrl($url);
                $page->setTitle($pageTitle);
                $page->setDescription($pageDescription);
                $imagePath = $this->getImageFromUrl($url);
                $page->setImageFile($imagePath);
                $pageIssues = $this->metaTagService->analyzeMetaTags($pageTags);
                $page->setIssues($pageIssues);
                $this->pageRepository->save($page);
                foreach ($pageTags as $tagName => $tagContent) {
                    $metaTag = new MetaTag();
                    $metaTag->setName($tagName);
                    $metaTag->setContent($tagContent);
                    $metaTag->setPage($page);
                    $this->metaTagRepository->saveAndPresist($metaTag);
                    $page->addMetaTag($metaTag);
                }
            }
            return ($page);
        } catch (Exception $exception) {
            $this->logger->error('An error occurred in saveAndAnalyze.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ]);
            throw $exception;
        }
    }


    /**
     * @param string $url
     * @param string|null $previousImage
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getImageFromUrl(string $url, string $previousImage = null): ?string
    {
        try {
            // check if previous image exist, then remove that
            if ($previousImage) {
                $destination = $this->parameterBag->get('kernel.project_dir').'/public/downloads/';
                // Get the image file path
                $imageFilePath = $destination.$previousImage;
                // Delete the file if it exists
                if ($imageFilePath != $destination && file_exists($imageFilePath)) {
                    $filesystem = new Filesystem();
                    $filesystem->remove($imageFilePath);
                }
            }

            $fileName = time() . '.png';
            $destination = $this->parameterBag->get('kernel.project_dir') . '/public/downloads/';
            // Check if the destination folder exists, create it if not
            if (!is_dir($destination)) {
                mkdir($destination, 0755, true);
            }
            $destination = $this->parameterBag->get('kernel.project_dir').'/public/downloads/';
            $image = new Image($url);
            $image->saveAs($destination . $fileName);
            $imageFilePath = $destination.$fileName;
            if(file_exists($imageFilePath)) {
                return $fileName;
            } else {
                return null;
            }
        }catch (Exception $exception) {
            $this->logger->error('An error occurred in getImageFromUrl.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ]);
            throw $exception;
        }
    }

    /**
     * @param Page $page
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function recheckPage(Page $page): string
    {
        try {
            $pageContent = $this->validateUrlAndGetContent($page->getUrl());
            $pageTitle = $pageContent['title'];
            $pageTags = $pageContent['metaTags'];
            $pageDescription = $pageContent['description'];

            // save page information
            $page->setTitle($pageTitle);
            $page->setDescription($pageDescription);
            // removing old meta tags as page content is newly extracted
            $this->pageRepository->removeAllMetaTags($page);
            // saving new meta tags
            $this->metaTagService->saveMetaTags($page, $pageTags);
            $page->setIssues($this->metaTagService->analyzeMetaTags($pageTags));
            $imageFile = $this->getImageFromUrl($page->getUrl(), $page->getImageFile());
            $page->setImageFile($imageFile);

            $this->pageRepository->save($page);
            return 'Page contents updated for page'. $page->getUrl();
        } catch (Exception $exception) {
            $this->logger->error('An error occurred in recheckPage.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ]);
            throw $exception;
        }
    }

    /**
     * @param $pageContent
     * @return string|null
     */
    public function extractPageTitle($pageContent): ?string
    {
        if ($pageContent === null) {
            return null;
        }
        return preg_match(self::TITLEREGEX,
            $pageContent, $matches)
            ? $matches[1] : null;
    }
}
