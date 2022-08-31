<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\OrderComponent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
    private $params;

    public function __construct(ManagerRegistry $registry, ParameterBagInterface $params)
    {
        $this->params = $params;
        
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

            return $queryBuilder->getQuery()->getResult();
        }
        else
        {
            return [];
        }
    }

    public function findOneWithEntitiesBy($where)
    {

        $queryBuilder = $this->createQueryBuilder('p')
            ->addSelect('u')
            ->addSelect('c')
            ->addSelect('pr')
            ->addSelect('s')
            ->addSelect('pv')
            ->addSelect('po')
            ->addSelect('f')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.price', 'pr')
            ->leftJoin('p.stocks', 's')
            ->leftJoin('p.propertyValues', 'pv')
            ->leftJoin('pv.property', 'po')
            ->leftJoin('p.file', 'f')
        ;
        
        foreach($where as $field => $value) {
            $queryBuilder
                ->andWhere('p.' . $field . ' = :value')
                ->setParameter('value', $value)
            ;
        }

        $queryBuilder
            ->addOrderBy('pr.date', 'DESC')
            ->addOrderBy('s.date', 'DESC')
        ;

        $result = $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if($result) {

            foreach($result->getFiles() as $file) {

                $host = null;

                switch($file->getSource()) {

                    case 'local_cdn':
                    default: {

                        $host = $this->params->get('local_cdn');

                    } break;
                }

                $file->setUrl($host . $file->getFileName() . '.' . $file->getExtension());

                switch($file->getMimeType()) {

                    case 'image/jpeg':
                    case 'image/png':
                    case 'image/gif': {

                        $file->setSquareThumbnailUrl($host . $file->getFileName() . '_thumb.' . $file->getExtension());
                        $file->setFixedHeightThumbnailUrl($host . $file->getFileName() . '_thumb_h.' . $file->getExtension());

                    } break;

                }

            }

            if($result->getNormalLevel() > 0 || $result->getWarningLevel() > 0 || $result->getCriticalLevel() > 0) {


                if($result->getNormalLevel() > 0 && $result->getStock() > 0) {
                    
                    $result->setStockLevel( (int) ( $result->getStock() * 100 / $result->getNormalLevel() ) );

                    if($result->getStockLevel() > 100) {

                        $result->setStockLevel(100);

                    }

                }

                if($result->getStock() <= 0) {
                    
                    $result->setStockLevelIndicator('out_of_stock');

                    if(!is_numeric($result->getStockLevel())) {
                        $result->setStockLevel(100);
                    }

                }
                elseif($result->getCriticalLevel() && $result->getCriticalLevel() >= $result->getStock()) {
                    
                    $result->setStockLevelIndicator('critical');

                    if(!is_numeric($result->getStockLevel())) {
                        $result->setStockLevel(10);
                    }

                }
                elseif($result->getWarningLevel() && $result->getWarningLevel() >= $result->getStock()) {
                    
                    $result->setStockLevelIndicator('warning');

                    if(!is_numeric($result->getStockLevel())) {
                        $result->setStockLevel(50);
                    }

                }
                elseif($result->getCriticalLevel() && $result->getCriticalLevel() >= $result->getStock()) {
                    
                    $result->setStockLevelIndicator('critical');

                    if(!is_numeric($result->getStockLevel())) {
                        $result->setStockLevel(10);
                    }

                }
                else {

                    $result->setStockLevelIndicator('normal');

                    if(!is_numeric($result->getStockLevel())) {
                        $result->setStockLevel(100);
                    }

                }

            }

            if(!$result->getStockLevelIndicator()) {
                if($result->getStock() > 10) {
                    $result->setStockLevel(100);
                    $result->setStockLevelIndicator('out_of_stock');
                }
                elseif($result->getStock() > 5) {
                    $result->setStockLevel(50);
                    $result->setStockLevelIndicator('warning');
                }
                elseif($result->getStock() > 1) {
                    $result->setStockLevel(10);
                    $result->setStockLevelIndicator('critical');
                }
                else {
                    $result->setStockLevel(0);
                    $result->setStockLevelIndicator('out_of_stock');
                }
            }
            
        }

        return $result;

    }

}