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

    public function flush()
    {
        $this->om->flush();
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
            $model->getWorkflowIdentifier(),
            $processName
        );

        return $modelState;
    }

    /**
     * Create a new successful model state.
     *
     * @param ModelInterface $model
     * @param string $processName
     * @param string $stepName
     * @return \FreeAgent\WorkflowBundle\Entity\ModelState
     */
    public function newModelStateSuccess(ModelInterface $model, $processName, $stepName)
    {
        $modelState = $this->createModelState($model, $processName, $stepName);
        $modelState->setSuccessful(true);

        $this->om->persist($modelState);
        $this->om->flush();

        return $modelState;
    }

    /**
     * Create a new invalid model state.
     *
     * @param ModelInterface $model
     * @param string $processName
     * @param string $stepName
     * @return \FreeAgent\WorkflowBundle\Entity\ModelState
     */
    public function newModelStateError(ModelInterface $model, $processName, $stepName, array $errors)
    {
        $messages = array();
        foreach ($errors as $error) {
            $messages[] = (string) $error;
        }

        $modelState = $this->createModelState($model, $processName, $stepName);
        $modelState->setSuccessful(false);
        $modelState->setErrors($messages);

        $this->om->persist($modelState);
        $this->om->flush();

        return $modelState;
    }

    /**
     * Create a new model state.
     *
     * @param ModelInterface $model
     * @param string $processName
     * @param string $stepName
     * @return \FreeAgent\WorkflowBundle\Entity\ModelState
     */
    protected function createModelState(ModelInterface $model, $processName, $stepName)
    {
        $modelState = new ModelState();
        $modelState->setWorkflowIdentifier($model->getWorkflowIdentifier());
        $modelState->setProcessName($processName);
        $modelState->setStepName($stepName);
        $modelState->setData($model->getWorkflowData());

        return $modelState;
    }
}