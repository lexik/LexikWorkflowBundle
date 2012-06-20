<?php

namespace FreeAgent\WorkflowBundle\Model;

use Doctrine\ORM\EntityRepository;

class ModelStateRepository extends EntityRepository
{
    /**
     * Returns the last ModelState for the given model hash.
     *
     * @param string $hash
     * @param string $processName
     * @return FreeAgent\WorkflowBundle\Entity\ModelState
     */
    public function findLatestModelState($hash, $processName)
    {
        $results = $this->createQueryBuilder('ms')
            ->andWhere('ms.hash = :hash')
            ->andWhere('ms.processName = :process')
            ->orderBy('ms.reachedAt', 'DESC')
            ->setParameter('hash', $hash)
            ->setParameter('process', $processName)
            ->getQuery()
            ->getResult();

        return isset($results[0]) ? $results[0] : null;
    }
}