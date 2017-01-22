<?php

namespace AppBundle\Repository;

/**
 * EpisodeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EpisodeRepository extends \Doctrine\ORM\EntityRepository
{
	// ************
	// Question 5 : 
	// ____________
	public function findByComingNext() {
		return $this->createQueryBuilder('episode')
			->where('episode.date > CURRENT_TIMESTAMP()')
			->orderBy('episode.date')
			->getQuery()
			->getResult();
	}
}