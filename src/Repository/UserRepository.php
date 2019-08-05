<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }



    public function findOneByLoginField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :val')
            ->orWhere('u.username = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return User[]
     */
    public function findBestUsersTotal() {
        return $this->createQueryBuilder('u')
            ->select('u.id', 'u.username', 'u.totalScore')
            ->orderBy("u.totalScore", "DESC")
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return User[]
     */
    public function findBestUsersMonth() {
        return $this->createQueryBuilder('u')
            ->select('u.id', "u.username", "u.monthScore")
            ->orderBy("u.monthScore", "DESC")
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param User|null $getUser
     */
    public function countUsersValidatedPosts(?User $user) {
        return $this->createQueryBuilder('u')
            ->join("u.posts", "p")
            ->select("count(p)")
            ->andWhere("u = :user")
            ->andWhere("p.validated = 1")
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param User $user
     * @return User
     */
    public function getAllUserData($user) {
        return $this->createQueryBuilder('u')
            ->leftjoin("u.posts", "p")
            ->leftJoin("p.tags", "t")
            ->leftJoin("p.likes", "l")
            ->leftJoin("u.badges", "b")
            ->select("u", "p", "b", "t", "l")
            ->andWhere("u = :user")
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


}
