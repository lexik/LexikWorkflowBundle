<?php

namespace Lexik\Bundle\WorkflowBundle\Model;

use Lexik\Bundle\WorkflowBundle\Entity\ModelState;

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
     * @return Lexik\Bundle\WorkflowBundle\Entity\ModelState
     */
    public function findCurrentModelState(ModelInterface $model, $processName)
    {
        return $this->repository->findLatestModelState(
            $model->getWorkflowIdentifier(),
            $processName
        );
    }

    /**
     * Returns all model states.
     *
     * @param ModelInterface $model
     * @param string $processName
     * @param string $successOnly
     * @return array
     */
    public function findAllModelStates(ModelInterface $model, $processName, $successOnly = true)
    {
        return $this->repository->findModelStates(
            $model->getWorkflowIdentifier(),
            $processName,
            $successOnly
        );
    }

    /**
     * Delete all model states.
     *
     * @param ModelInterface $model
     * @param string $processName
     */
    public function deleteAllModelStates(ModelInterface $model, $processName = null)
    {
        return $this->repository->deleteModelStates(
            $model->getWorkflowIdentifier(),
            $processName
        );
    }

    /**
     * Create a new successful model state.
     *
     * @param ModelInterface $model
     * @param string $processName
     * @param string $stepName
     * @param ModelState $previous
     * @return \Lexik\Bundle\WorkflowBundle\Entity\ModelState
     */
    public function newModelStateSuccess(ModelInterface $model, $processName, $stepName, $previous = null)
    {
        $modelState = $this->createModelState($model, $processName, $stepName, $previous);
        $modelState->setSuccessful(true);

        $this->om->persist($modelState);
        $this->om->flush($modelState);

        return $modelState;
    }

    /**
     * Create a new invalid model state.
     *
     * @param ModelInterface $model
     * @param string $processName
     * @param string $stepName
     * @param ModelState $previous
     * @param array $errors
     * @return \Lexik\Bundle\WorkflowBundle\Entity\ModelState
     */
    public function newModelStateError(ModelInterface $model, $processName, $stepName, array $errors, $previous = null)
    {
        $messages = array();
        foreach ($errors as $error) {
            $messages[] = (string) $error;
        }

        $modelState = $this->createModelState($model, $processName, $stepName, $previous);
        $modelState->setSuccessful(false);
        $modelState->setErrors($messages);

        $this->om->persist($modelState);
        $this->om->flush($modelState);

        return $modelState;
    }

    /**
     * Create a new model state.
     *
     * @param ModelInterface $model
     * @param string $processName
     * @param string $stepName
     * @param ModelState $previous
     * @return \Lexik\Bundle\WorkflowBundle\Entity\ModelState
     */
    protected function createModelState(ModelInterface $model, $processName, $stepName, $previous = null)
    {
        $modelState = new ModelState();
        $modelState->setWorkflowIdentifier($model->getWorkflowIdentifier());
        $modelState->setProcessName($processName);
        $modelState->setStepName($stepName);
        $modelState->setData($model->getWorkflowData());

        if ($previous instanceof ModelState) {
            $modelState->setPrevious($previous);
        }

        return $modelState;
    }
}