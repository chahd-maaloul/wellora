<?php

namespace App\Repository;

use App\Entity\DailyPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Goal;
/**
 * @extends ServiceEntityRepository<DailyPlan>
 */
class DailyPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyPlan::class);
    }
/**
 * Find users who have active goals (status 'PENDING' or 'in progress') with their goals
 */
public function findUsersWithActiveGoals(): array
{
    return $this->createQueryBuilder('dp')
        ->select('DISTINCT u, g')
        ->from('App\Entity\User', 'u')
        ->innerJoin('u.goals', 'g')
        ->where('g.status IN (:statuses)')
        ->setParameter('statuses', ['PENDING', 'in progress'])
        ->orderBy('u.firstName', 'ASC')
        ->addOrderBy('u.lastName', 'ASC')
        ->getQuery()
        ->getResult();
}
    //    /**
    //     * @return DailyPlan[] Returns an array of DailyPlan objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DailyPlan
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    // src/Repository/DailyPlanRepository.php

public function findByUser(User $user): array
{
    return $this->createQueryBuilder('d')
        ->leftJoin('d.goal', 'g')
        ->where('g.patient = :user')
        ->setParameter('user', $user)
        ->orderBy('d.date', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findByUserAndGoal(User $user, Goal $goal): array
{
    return $this->createQueryBuilder('d')
        ->where('d.goal = :goal')
        ->andWhere('d.goal IN (
            SELECT g.id FROM App\Entity\Goal g
            WHERE g.patient = :user
        )')
        ->setParameter('goal', $goal)
        ->setParameter('user', $user)
        ->orderBy('d.date', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findWeeklyStatsByUser(User $user): array
{
    $today = new \DateTime();
    $startOfWeek = clone $today;
    $startOfWeek->modify('Monday this week')->setTime(0, 0, 0);
    $endOfWeek = clone $startOfWeek;
    $endOfWeek->modify('+6 days')->setTime(23, 59, 59);
    
    $qb = $this->createQueryBuilder('d')
        ->leftJoin('d.goal', 'g')
        ->where('g.patient = :user')
        ->andWhere('d.date BETWEEN :start AND :end')
        ->setParameter('user', $user)
        ->setParameter('start', $startOfWeek)
        ->setParameter('end', $endOfWeek);
    
    $plans = $qb->getQuery()->getResult();
    
    return $this->calculateStats($plans);
}

private function calculateStats(array $plans): array
{
    $stats = [
        'scheduled' => 0,
        'completed' => 0,
        'minutes' => 0,
        'restDays' => 0,
        'calories' => 0
    ];
    
    foreach ($plans as $plan) {
        if ($plan->getExercices()->count() === 0) {
            $stats['restDays']++;
        } else {
            $stats['scheduled']++;
            
            if ($plan->getStatus() === 'completed') {
                $stats['completed']++;
            }
            
            $stats['minutes'] += $plan->getDureeMin() ?? 0;
            $stats['calories'] += $plan->getCalories() ?? 0;
        }
    }
    
    return $stats;
}
}

