<?php

namespace FreeAgent\WorkflowBundle\Model;

use Doctrine\ORM\EntityRepository;

class ModelStateRepository extends EntityRepository
{
    /**
     * Returns the last ModelState for the given workflow identifier.
     *
     * @param string $workflowIdentifier
     * @param string $processName
     * @return FreeAgent\WorkflowBundle\Entity\ModelState
     */
    public function findLatestModelState($workflowIdentifier, $processName)
    {
        $results = $this->createQueryBuilder('ms')
            ->andWhere('ms.workflowIdentifier = :workflow_identifier')
            ->andWhere('ms.processName = :process')
            ->orderBy('ms.createdAt', 'DESC')
            ->setParameter('workflow_identifier', $workflowIdentifier)
            ->setParameter('process', $processName)
            ->getQuery()
            ->getResult();

        return isset($results[0]) ? $results[0] : null;
    }
}