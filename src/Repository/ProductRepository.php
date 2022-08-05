<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByKeywords($query): array
    {

        $tmpKeywords = explode(' ', $query);
        $keywords = [];

        foreach($tmpKeywords as $keyword) {
            if(strlen(trim($keyword)) > 2) {
                $keywords[] = trim($keyword);
            }
        }

        if(count($keywords) > 0)
        {

            $queryBuilder = $this->createQueryBuilder('p');

            $i = 0;
            foreach ($keywords as $keyword) {

                if($i == 0)
                {
                    $queryBuilder
                        ->andWhere('p.name LIKE :keyword_' . $i . ' OR p.sku = :keyword_' . $i . ' OR p.ean = :keyword_' . $i . ' OR p.gtin = :keyword_' . $i . ' OR p.isbn = :keyword_' . $i);
                }
                else
                {
                    $queryBuilder
                        ->orWhere('p.name LIKE :keyword_' . $i . ' OR p.sku = :keyword_' . $i . ' OR p.ean = :keyword_' . $i . ' OR p.gtin = :keyword_' . $i . ' OR p.isbn = :keyword_' . $i);
                }
                $queryBuilder
                    ->andWhere('p.category IS NOT NULL')
                    ->setParameter(':keyword_' . $i, '%' . $keyword . '%');
                
                $i++;
            }
            
            //dd($queryBuilder->getQuery());

            return $queryBuilder->getQuery()->getResult();
        }
        else
        {
            return [];
        }
    }


//    /**
//     * @return Product[] Returns an array of Product objects
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

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
