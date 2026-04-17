<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findByDate(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.dateRdv = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('r.heureRdv', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function existsForSlot(\DateTimeInterface $date, string $heureRdv): bool
    {
        $count = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.dateRdv = :d')
            ->andWhere('r.heureRdv = :h')
            ->setParameter('d', $date->format('Y-m-d'))
            ->setParameter('h', $heureRdv)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }
}
