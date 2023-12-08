<?php

namespace App\Tests\Service;

use App\Entity\MetaTag;
use App\Entity\Page;
use App\Service\MetaTagService;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MetaTagRepository;

/**
 *
 */
class MetaTagServiceTest extends TestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MetaTagRepository
     */
    private $metaTagRepository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->metaTagRepository = $this->createMock(MetaTagRepository::class);
    }

    /**
     * @return void
     */
    public function testGetMetaTagsByPageId()
    {
        $pageId = 1;
        $expectedResult = ['tag1' => 'value1', 'tag2' => 'value2'];

        $this->metaTagRepository->expects($this->once())
            ->method('findMetaTagsByPageId')
            ->with($this->equalTo($pageId))
            ->willReturn($expectedResult);

        $metaTagService = new MetaTagService($this->entityManager, $this->metaTagRepository);
        $result = $metaTagService->getMetaTagsByPageId($pageId);
        $this->assertEquals($expectedResult, $result);
    }
}
