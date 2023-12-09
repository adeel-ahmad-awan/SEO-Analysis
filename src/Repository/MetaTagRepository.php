<?php

namespace App\Repository;

use App\Entity\MetaTag;
use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MetaTag>
 *
 * @method MetaTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method MetaTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method MetaTag[]    findAll()
 * @method MetaTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MetaTagRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, MetaTag::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @param $pageId
     * @return array
     */
    public function findMetaTagsByPageId($pageId): array
    {
        $page = $this->entityManager->getRepository(Page::class)->find($pageId);
        if (!$page) {
            return [];
        }
        $metaTags = $page->getMetaTag();
        return $metaTags->toArray();
    }

    /**
     * @param MetaTag $metaTag
     * @return void
     */
    function saveAndPresist(MetaTag $metaTag): void
    {
        $this->entityManager->persist($metaTag);
        $this->entityManager->flush();
    }

//    /**
//     * @return MetaTag[] Returns an array of MetaTag objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MetaTag
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
