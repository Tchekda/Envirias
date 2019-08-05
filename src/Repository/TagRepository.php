<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Tag::class);
    }


    public function findMostPopular()
    {
        return $this->createQueryBuilder('t')
            ->join("t.posts", "p")
            ->select("count(p.id)","t")
            ->andWhere("p.validated = 1")
            ->groupBy('t.id')
            ->orderBy("count(p.id)", "DESC")
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }


    /**
     * @return string[]
     */
    public function findAllWithNames() {
        return $this->createQueryBuilder('t')
            ->select("t.name")
            ->orderBy("t.canonical", "ASC")
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Tag $tag
     */
    public function findAllPostByTag(Tag $tag) {
        return $this->createQueryBuilder('t')
            ->join("t.posts", "p")
            ->join("p.user", "u")
            ->leftJoin("u.badges", "b")
            ->select("t", "p", "u", "b")
            ->andWhere("t.id = :tag")
            ->andWhere("p.validated = 1")
            ->setParameter('tag', $tag->getId())
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


    /*
    public function findOneBySomeField($value): ?Tag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
