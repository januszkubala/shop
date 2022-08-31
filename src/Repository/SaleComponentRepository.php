<?php

namespace App\Repository;

use App\Entity\SaleComponent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleComponent>
 *
 * @method SaleComponent|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleComponent|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleComponent[]    findAll()
 * @method SaleComponent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleComponentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleComponent::class);
    }

    public function add(SaleComponent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleComponent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
}
