<?php

namespace App\Tests\Service;

use App\Entity\Page;
use App\Repository\PageRepository;
use App\Service\MetaTagService;
use App\Service\PageService;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * PageServiceTest
 */
class PageServiceTest extends TestCase
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var MetaTagService
     */
    private $metaTagService;

    /**
     * logger
     */
    private $logger;

    /**
     * @var ContainerBagInterface
     */
    private $parameterBag;

    /**
     * @var string
     */
    private string $validUrl = 'https://www.google.com/';

    /**
     * @var string
     */
    private string $invalidUrl = 'invalid Url';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->pageRepository = $this->createMock(PageRepository::class);
        $this->metaTagService = $this->createMock(MetaTagService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->parameterBag = $this->createMock(ContainerBagInterface::class);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testValidateUrlAndGetContentWithValidUrl()
    {
        $metaTags = ['title' => 'title', 'description' => 'description'];
        $this->metaTagService->expects($this->once())->method('getMetaTags')->willReturn($metaTags);

        // Create PageService instance with mocks
        $pageService = new PageService(
            $this->pageRepository,
            $this->metaTagService,
            $this->logger,
            $this->parameterBag
        );

        $result = $pageService->validateUrlAndGetContent($this->validUrl);

        $expectedResult = [
            'title' => 'Google',
            'metaTags' => $metaTags,
            'description' => 'description'
        ];

        $this->assertEquals($expectedResult, $result);
    }


    public function testValidateUrlAndGetContentWithInvalidUrl()
    {
        // Create PageService instance with mocks
        $pageService = new PageService(
            $this->pageRepository,
            $this->metaTagService,
            $this->logger,
            $this->parameterBag
        );
        $result = $pageService->validateUrlAndGetContent($this->invalidUrl);
        $expectedResult = [
            'title' => null,
            'metaTags' => [],
            'description' => null
        ];
        $this->assertEquals($expectedResult, $result);
    }
}
