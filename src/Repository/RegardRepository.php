<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Regard;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Regard|null find($id, $lockMode = null, $lockVersion = null)
 * @method Regard|null findOneBy(array $criteria, array $orderBy = null)
 * @method Regard[]    findAll()
 * @method Regard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegardRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Regard::class);
    }

    public function findByAuthorAndTarget(User $author, Article $target): ?Regard
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.author = :author')
            ->setParameter('author', $author)
            ->andWhere('r.target = :target')
            ->setParameter('target', $target)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    // /**
    //  * @return Regard[] Returns an array of Regard objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Regard
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
