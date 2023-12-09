<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use function PHPUnit\Framework\throwException;

/**
 * @extends ServiceEntityRepository<Page>
 *
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{

    protected LoggerInterface $logger;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Page::class);
        $this->logger = $logger;
    }

    /**
     * @param $url
     * @return Page|null
     */
    public function findOneByUrl($url): ?Page
    {
        return $this->findOneBy(['url' => $url]);
    }

    /**
     * @param Page $page
     * @return bool
     * @throws Exception
     */
    public function save(Page $page): bool
    {
        try {
            $this->getEntityManager()->persist($page);
            $this->getEntityManager()->flush();
            return true;
        } catch (Exception $exception) {
            $this->logger->error('An error occurred in function save.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ]);
            throw $exception;
        }
    }

    public function removeAllMetaTags(Page $page): void
    {
        foreach ($page->getMetaTag() as $metaTag) {
            $page->removeMetaTag($metaTag);
            $this->getEntityManager()->remove($metaTag);
        }
        $this->getEntityManager()->flush();
    }
}
