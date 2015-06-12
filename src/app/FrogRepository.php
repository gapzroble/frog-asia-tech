<?php

namespace FrogAsia;

use Doctrine\ORM\EntityRepository;

/**
 * @author Randolph Roble <roblerm@gmail.com>
 */
class FrogRepository extends EntityRepository
{

    public function findAll($status = null, $gender = null, $page = 1, $sort = 'name', $dir = 'asc', $limit = 10)
    {
        if (!in_array($sort, ['id', 'name', 'gender', 'birthdate'])) {
            $sort = 'name';
        }
        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }
        $qb = $this->createQueryBuilder('f')
                ->orderBy('f.'.$sort, $dir)
                ->setMaxResults(10)
                ->setFirstResult(($page-1)*$limit);
        if ($status == 'alive') {
            $qb->andWhere('f.death is null');
        } elseif ($status == 'dead') {
            $qb->andWhere('f.death is not null');
        }
        if ($gender == Frog::GENDER_FEMALE || $gender == Frog::GENDER_MALE) {
            $qb->andWhere('f.gender = :gender')
                    ->setParameter('gender', $gender);
        }
        return $qb->getQuery();
    }
    
    public function findAllAlive()
    {
        $qb = $this->createQueryBuilder('f')
                ->where('f.death is null')
                ->orderBy('f.name');
        return $qb->getQuery()->getResult();
    }

}
