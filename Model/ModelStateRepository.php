<?php

namespace FreeAgent\WorkflowBundle\Model;

use Doctrine\ORM\EntityRepository;

class ModelStateRepository extends EntityRepository
{
    /**
     * Returns the last ModelState for the given model hash?
     *
     * @param string $hash
     * @param string $processName
     * @param string $stepName
     * @return FreeAgent\WorkflowBundle\Entity\ModelState
     */
    public function findLatestModelState($hash, $processName, $stepName)
    {
        $results = $this->createQueryBuilder('ms')
            ->andWhere('ms.hash = :hash')
            ->andWhere('ms.processName = :process')
            ->orderBy('ms.reachedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return isset($results[0]) ? $results[0] : null;
    }
}