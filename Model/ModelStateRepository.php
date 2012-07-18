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
            ->andWhere('ms.successful = :success')
            ->orderBy('ms.id', 'DESC')
            ->setParameter('workflow_identifier', $workflowIdentifier)
            ->setParameter('process', $processName)
            ->setParameter('success', true)
            ->getQuery()
            ->getResult();

        return isset($results[0]) ? $results[0] : null;
    }

    /**
     * Returns all model states for the given workflow identifier.
     *
     * @param string $workflowIdentifier
     * @param string $processName
     * @param boolean $successOnly
     * @return array
     */
    public function findModelStates($workflowIdentifier, $processName, $successOnly)
    {
        $qb = $this->createQueryBuilder('ms')
            ->andWhere('ms.workflowIdentifier = :workflow_identifier')
            ->andWhere('ms.processName = :process')
            ->orderBy('ms.createdAt', 'ASC')
            ->setParameter('workflow_identifier', $workflowIdentifier)
            ->setParameter('process', $processName)
        ;

        if ($successOnly) {
            $qb->andWhere('ms.successful = :success')
                ->setParameter('success', true);
        }

        return $qb->getQuery()->getResult();
    }
}