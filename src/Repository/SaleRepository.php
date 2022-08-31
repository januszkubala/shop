<?php

namespace App\Repository;

use App\Entity\Sale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sale>
 *
 * @method Sale|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sale|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sale[]    findAll()
 * @method Sale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sale::class);
    }

    public function add(Sale $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sale $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllSales(): array
    {

        return $queryBuilder = $this->createQueryBuilder('o')
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

    }

    public function findFilteredSales($filters): array
    {

        $queryBuilder = $this->createQueryBuilder('o');

        if($filters['query'] != null) {

            $queryKeywords = explode(' ', $filters['query']);
            $keywords = [];
    
            foreach($queryKeywords as $keyword) {
                if(strlen(trim($keyword)) > 2) {
                    $keywords[] = trim($keyword);
                }
            }

            if(count($keywords) > 0){
    
                $i = 0;
                foreach ($keywords as $keyword) {
                    if($i == 0){
                        $queryBuilder
                            ->andWhere('o.first_name LIKE :keyword_' . $i . ' OR o.last_name LIKE :keyword_' . $i);
                    }
                    else{
                        $queryBuilder
                            ->orWhere('o.first_name LIKE :keyword_' . $i . ' OR o.last_name LIKE :keyword_' . $i);
                    }
                    $queryBuilder
                        ->setParameter(':keyword_' . $i, '%' . $keyword . '%');
                    $i++;
                }
            }
        }

        if($filters['grossValueBetween'] != null || $filters['grossValueAnd'] != null) {
            if($filters['grossValueBetween'] != null) {
                $queryBuilder
                    ->andWhere('o.amount >= :value_between')
                    ->setParameter('value_between', $filters['grossValueBetween'])
                ;
            }
            if($filters['grossValueAnd'] != null) {
                $queryBuilder
                    ->andWhere('o.amount <= :value_and')
                    ->setParameter('value_and', $filters['grossValueAnd'])
                ;
            }
        }

        if($filters['dateBetween'] != null || $filters['dateAnd']) {
            if($filters['dateBetween'] != null) {

                $queryBuilder
                    ->andWhere('o.date >= :date_between')
                    ->setParameter('date_between', $filters['dateBetween']->format('Y-m-d 00:00:00'))
                ;
            }
            if($filters['dateAnd'] != null) {
                $queryBuilder
                    ->andWhere('o.date <= :date_and')
                    ->setParameter('date_and', $filters['dateAnd']->format('Y-m-d 23:59:59'))
                ;
            }
        }

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;

    }

    public function findLastSalesForProduct($product, $limit): ?array
    {

        $queryBuilder = $this->createQueryBuilder('o')
            //->addSelect('oc')
            //->leftJoin('App\Entity\OrderComponent', 'oc')
            //->where('oc.product = 1')
        ;
/*
        $queryBuilder
            ->where('oc.product = :value')
            ->setParameter('value', 1)
        ;
*/

        //dd($queryBuilder->getQuery());

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;

    }

}
