<?php

namespace App\Controller;

use App\Form\PreviewType;
use App\Service\PageService;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 *
 */
class SeoAnalysisController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @param Request $request
     * @param PageService $pageService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/preview', name: 'preview')]
    public function preview(Request $request, PageService $pageService)
    {
        $form = $this->createForm(PreviewType::class);
        $form->handleRequest($request);
        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();

                $url = $formData['url'];
                $page = $pageService->saveAndAnalyze($url);

                return $this->render('seoAnalysis/preview.html.twig', [
                    'form' => $form->createView(),
                    'previewImage' => $page->getImageFile(),
                ]);
            }
        } catch (Exception $exception) {
            $this->logger->error('An error occurred while saving the page.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ]);
            $this->addFlash('error', 'An error occurred while processing your request. Please try again.');
        }
        return $this->render('seoAnalysis/preview.html.twig', [
            'form' => $form->createView(),
            'previewImage' => null,

        ]);
    }

    /**
     * @param Request $request
     * @param PageService $metaTagAnalyzerService
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/api/analyze', name: 'app_seo_analysis', methods: ['POST'])]
    public function analyze(Request $request, PageService $pageService): Response
    {
        try {
            // Retrieve URL from request
            $url = $request->request->get('url');
            if (empty($url)) {
                $jsonResponse = ['error' => 'URL is required.'];
                $responseCode = Response::HTTP_BAD_REQUEST;
            } elseif (!$pageService->validUrl($url)) {
                $jsonResponse = ['error' => 'Invalid page url provided, please provide a valid url'];
                $responseCode = Response::HTTP_BAD_REQUEST;
            } else {
                $page = $pageService->saveAndAnalyze($url);
                $jsonResponse = [
                    'Url' => $page->getUrl(),
                    'title' => $page->getTitle(),
                    'description' => $page->getDescription(),
                    'issues' => $page->getIssues(),
                    'meta_tags' => $page->metaTagsToArray()
                ];
                $responseCode = Response::HTTP_OK;
            }
        } catch (Exception $exception) {
            $this->logger->error('An error occurred while saving the page.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ]);
            $jsonResponse = ['error' => 'An error occurred during SEO analysis.'];
            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        return $this->json($jsonResponse, $responseCode);
    }
}
