<?php

namespace Lexik\Bundle\WorkflowBundle\Model;

use Doctrine\ORM\EntityRepository;

use Lexik\Bundle\WorkflowBundle\Entity\ModelState;

class ModelStateRepository extends EntityRepository
{
    /**
     * Returns the last ModelState for the given workflow identifier.
     *
     * @param string $workflowIdentifier
     * @param string $processName
     * @param string $stepName
     *
     * @return Lexik\Bundle\WorkflowBundle\Entity\ModelState
     */
    public function findLatestModelState($workflowIdentifier, $processName, $stepName = null)
    {
        $qb = $this->createQueryBuilder('ms');
        $qb
            ->andWhere('ms.workflowIdentifier = :workflow_identifier')
            ->andWhere('ms.processName = :process')
            ->andWhere('ms.successful = :success')
            ->orderBy('ms.id', 'DESC')
            ->setParameter('workflow_identifier', $workflowIdentifier)
            ->setParameter('process', $processName)
            ->setParameter('success', true);

        if (null !== $stepName) {
            $qb
                ->andWhere('ms.stepName = :stepName')
                ->setParameter('stepName', $stepName);
        }

        $results = $qb->getQuery()->getResult();

        return isset($results[0]) ? $results[0] : null;
    }

    /**
     * Returns all model states for the given workflow identifier.
     *
     * @param  string  $workflowIdentifier
     * @param  string  $processName
     * @param  boolean $successOnly
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

    /**
     * Delete all model states for the given workflowIndentifier (and process name if given).
     *
     * @param  string $workflowIdentifier
     * @param  string $processName
     * @return int
     */
    public function deleteModelStates($workflowIdentifier, $processName = null)
    {
        $qb = $this->_em->createQueryBuilder()
            ->delete($this->_entityName, 'ms')
            ->andWhere('ms.workflowIdentifier = :workflow_identifier')
            ->setParameter('workflow_identifier', $workflowIdentifier);

        if (null !== $processName) {
            $qb->andWhere('ms.processName = :process')
                ->setParameter('process', $processName);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Normalize by fetching workflow states of each $objects.
     *
     * @param ModelState|array $objects
     * @param array            $processes
     * @param bool             $onlySuccess
     *
     * @throws \InvalidArgumentException
     */
    public function setStates($objects, $processes, $onlySuccess)
    {
        $objects = ( ! is_array($objects) && ! $objects instanceof \ArrayAccess) ? array($objects) : $objects;

        if (0 === count($objects)) {
            return;
        }

        $ordersIndexedByWorkflowIdentifier = array();
        foreach ($objects as $object) {
            if (!$object instanceof ModelStateInterface) {
                throw new \InvalidArgumentException();
            }

            $ordersIndexedByWorkflowIdentifier[$object->getWorkflowIdentifier()] = $object;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ms')
            ->from('Lexik\Bundle\WorkflowBundle\Entity\ModelState', 'ms')
            ->andWhere($qb->expr()->in('ms.workflowIdentifier', array_keys($ordersIndexedByWorkflowIdentifier)))
            ->orderBy('ms.id');

        if (count($processes)) {
            $qb->andWhere($qb->expr()->in('ms.processName', $processes));
        }

        if ($onlySuccess) {
            $qb->andWhere('ms.successful = 1');
        }

        $states = $qb->getQuery()->getResult();

        foreach ($states as $state) {
            $ordersIndexedByWorkflowIdentifier[$state->getWorkflowIdentifier()]->addState($state);
        }
    }
}
