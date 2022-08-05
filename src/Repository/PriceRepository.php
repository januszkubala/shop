<?php

namespace App\Repository;

use App\Entity\Price;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Price>
 *
 * @method Price|null find($id, $lockMode = null, $lockVersion = null)
 * @method Price|null findOneBy(array $criteria, array $orderBy = null)
 * @method Price[]    findAll()
 * @method Price[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Price::class);
    }

    public function add(Price $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Price $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findCurrentPrice($product): ?object
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->addSelect('t')
            ->andWhere('p.product = :product')
            ->setParameter('product', $product->getId())
            ->leftJoin('p.tax', 't')
            ->orderBy('p.date', 'DESC')
        ;

        $prices = $queryBuilder->getQuery()->getResult();

        $currentPrice = null;

        foreach($prices as $price) {

            if($price->getDateValidFrom() != null) {

                if($price->getDateValidFrom() < new \DateTime()) {
                    
                    if($price->getDateValidTo() != null) {
                        if($price->getDateValidTo() > new \DateTime()) {
                            $currentPrice = $price;
                        }
                    }
                    else {
                        $currentPrice = $price;
                    }

                }
            }
            elseif($currentPrice == null) {
                $currentPrice = $price;
            }

        }

        return $currentPrice;
    }

}

//    /**
//     * @return Price[] Returns an array of Price objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Price
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }