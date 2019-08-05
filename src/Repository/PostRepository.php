<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function findAllValidated() {
        return $this->createQueryBuilder('p')
            ->andWhere('p.validated = 1')
            ->orderBy("p.createdAt", "DESC")
            ->getQuery()
            ->getResult();
    }


    /**
     * @return Post[] Returns an array of Post objects
     */
    public function findAllNotValidated()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.validated = 0')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllValidatedQuery(): QueryBuilder {
        return $this->createQueryBuilder('p')
            ->join("p.user", "u")
            ->leftJoin("p.likes", "l")
            ->leftJoin("p.tags", "t")
            ->leftJoin("u.badges", "b")
            ->select("p", "u", "l", "t", "b")
            ->andWhere('p.validated = 1')
            ->orderBy("p.createdAt", "DESC")
            ;
    }

}
