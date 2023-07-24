<?php

namespace App\Repository;

use App\Entity\MediaTrick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MediaTrick>
 *
 * @method MediaTrick|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaTrick|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaTrick[]    findAll()
 * @method MediaTrick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaTrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaTrick::class);
    }

    public function save(MediaTrick $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MediaTrick $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
