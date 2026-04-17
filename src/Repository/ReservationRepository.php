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
}
