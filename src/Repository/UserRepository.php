<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    public function findAllAndSortBy($field, $sort): array
    {   

        $queryBuilder = $this->createQueryBuilder('u')
            ->orderBy('u.' . $field, $sort)
        ;

        return $queryBuilder->getQuery()->getResult();

    }


    public function findFilteredUsers($filters): array
    {

        $queryBuilder = $this->createQueryBuilder('u');

        if($filters['query'] != null) {

            $queryKeywords = explode(' ', $filters['query']);
            $keywords = [];
    
            foreach($queryKeywords as $keyword) {
                if(strlen(trim($keyword)) > 2) {
                    $keywords[] = trim($keyword);
                }
            }

            if(count($keywords) > 0) {
    
                $i = 0;
                foreach ($keywords as $keyword) {
                    if($i == 0){
                        $queryBuilder
                            ->andWhere('u.first_name LIKE :keyword_' . $i . ' OR u.last_name LIKE :keyword_' . $i);
                    }
                    else{
                        $queryBuilder
                            ->orWhere('u.first_name LIKE :keyword_' . $i . ' OR u.last_name LIKE :keyword_' . $i);
                    }
                    $queryBuilder
                        ->setParameter(':keyword_' . $i, '%' . $keyword . '%');
                    $i++;
                }
            }
        }

        if($filters['email'] != null) {
            $queryBuilder
                ->andWhere('u.email LIKE :value')
                ->setParameter(':value', $filters['email']);
        }

        if($filters['role'] != null) {
            $queryBuilder
                ->andWhere('u.roles LIKE :value')
                ->setParameter(':value', '%' . $filters['role'] . '%');
        }

        return $queryBuilder->getQuery()
            ->getResult()
        ;

    }




//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
