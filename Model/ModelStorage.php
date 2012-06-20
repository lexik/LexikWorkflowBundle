<?php

namespace FreeAgent\WorkflowBundle\Model;

use FreeAgent\WorkflowBundle\Entity\ModelState;

use Doctrine\ORM\EntityManager;

class ModelStorage
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $om;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * Construct.
     *
     * @param EntityManager $om
     * @param string $entityClass
     */
    public function __construct(EntityManager $om, $entityClass)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository($entityClass);
    }

    /**
     * Returns the current model state.
     *
     * @param ModelInterface $model
     * @param string $processName
     * @return FreeAgent\WorkflowBundle\Entity\ModelState
     */
    public function findCurrentModelState(ModelInterface $model, $processName)
    {
        $modelState = $this->repository->findLatestModelState(
            $model->getIdentifier(),
            $processName
        );

        return $modelState;
    }

    /**
     * Create a new model state.
     *
     * @param ModelInterface $model
     * @param string $processName
     * @param string $stepName
     * @param mixed $data
     * @return \FreeAgent\WorkflowBundle\Entity\ModelState
     */
    public function newModelState(ModelInterface $model, $processName, $stepName, $data)
    {
        $modelState = new ModelState();
        $modelState->setHash($model->getIdentifier());
        $modelState->setProcessName($processName);
        $modelState->setStepName($stepName);
        $modelState->setData(is_array($data) ? json_encode($data) : $data);

        $this->om->persist($modelState);
        $this->om->flush();

        return $modelState;
    }
}