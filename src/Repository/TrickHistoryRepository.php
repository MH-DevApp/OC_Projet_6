<?php

namespace App\Repository;

use App\Entity\TrickHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TrickHistory>
 *
 * @method TrickHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrickHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrickHistory[]    findAll()
 * @method TrickHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrickHistory::class);
    }

    public function save(TrickHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TrickHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
