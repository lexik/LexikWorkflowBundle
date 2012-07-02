<?php

namespace FreeAgent\WorkflowBundle\Handler;

use Symfony\Component\Security\Core\SecurityContextInterface;

use FreeAgent\WorkflowBundle\Exception\WorkflowException;
use FreeAgent\WorkflowBundle\Exception\AccessDeniedException;
use FreeAgent\WorkflowBundle\Exception\ValidationException;
use FreeAgent\WorkflowBundle\Flow\Step;
use FreeAgent\WorkflowBundle\Flow\Process;
use FreeAgent\WorkflowBundle\Entity\ModelState;
use FreeAgent\WorkflowBundle\Model\ModelStorage;
use FreeAgent\WorkflowBundle\Model\ModelInterface;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Contains all logic to handle a process and its steps.
 */
class ProcessHandler implements ProcessHandlerInterface
{
    /**
     * @var FreeAgent\WorkflowBundle\Flow\Process
     */
    protected $process;

    /**
     * @var FreeAgent\WorkflowBundle\Model\ModelStorage
     */
    protected $storage;

    /**
     * @var Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $security;

    /**
     * Construct.
     *
     * @param Process      $process
     * @param ModelStorage $storage
     */
    public function __construct(Process $process, ModelStorage $storage)
    {
        $this->process = $process;
        $this->storage = $storage;
    }

    /**
     * Set security context.
     *
     * @param SecurityContextInterface $security
     */
    public function setSecurityContext(SecurityContextInterface $security)
    {
        $this->security = $security;
    }

    /**
     * @see FreeAgent\WorkflowBundle\Handler.ProcessHandlerInterface::start()
     */
    public function start(ModelInterface $model)
    {
        $modelState = $this->storage->findCurrentModelState($model, $this->process->getName());

        if ($modelState instanceof ModelState) {
            throw new WorkflowException('The given model as already started this process.');
        }

        $step = $this->getProcessStep($this->process->getStartStep());

        return $this->reachStep($model, $step);
    }

    /**
     * @see FreeAgent\WorkflowBundle\Handler.ProcessHandlerInterface::reachStep()
     */
    public function reachNextState(ModelInterface $model, $stateName)
    {
        $modelState = $this->storage->findCurrentModelState($model, $this->process->getName());

        if ( ! ($modelState instanceof ModelState)) {
            throw new WorkflowException('The given model has not started this process.');
        }

        $currentStep = $this->getProcessStep($modelState->getStepName());

        if (!$currentStep->hasNextState($stateName)) {
            throw new WorkflowException(sprintf('The step "%s" does not contain any next state named "%s".', $currentStep->getName(), $stateName));
        }

        $state = $currentStep->getNextState($stateName);

        // pre validations
        $errors = $this->executeValidations($model, $state->getAdditionalValidations());
        $modelState = null;

        if (count($errors) > 0) {
            $modelState = $this->storage->newModelStateError($model, $this->process->getName(), $state->getTarget()->getName(), $errors);
        } else {
            $modelState = $this->reachStep($model, $state->getTarget());
        }

        return $modelState;
    }

    /**
     * Reach the given step.
     *
     * @param ModelInterface $model
     * @param Step $step
     * @return FreeAgent\WorkflowBundle\Entity
     */
    protected function reachStep(ModelInterface $model, Step $step)
    {
        $this->checkCredentials($step);

        $errors = $this->executeValidations($model, $step->getValidations());

        if (0 === count($errors)) {
            $modelState = $this->storage->newModelStateSuccess($model, $this->process->getName(), $step->getName());

            // run actions
            foreach ($step->getActions() as $action) {
                list($service, $method) = $action;
                $service->$method($model, $step);
            }

        } else {
            $modelState = $this->storage->newModelStateError($model, $this->process->getName(), $step->getName(), $errors);

            if ($step->getOnInvalid()) {
                $step = $this->getProcessStep($step->getOnInvalid());
                $modelState = $this->reachStep($model, $step);
            }
        }

        return $modelState;
    }

    /**
     * Returns a step by its name.
     *
     * @param string $stepName
     * @return FreeAgent\WorkflowBundle\Flow\Step
     */
    protected function getProcessStep($stepName)
    {
        $step = $this->process->getStep($stepName);

        if (! ($step instanceof Step)) {
            throw new WorkflowException(sprintf('Can\'t find step named "%s" in process "%s".', $stepName, $this->process->getName()));
        }

        return $step;
    }

    /**
     * Execute some validations.
     *
     * @param ModelInterface $model
     * @param array          $validations
     *
     * @return array An array of validation exceptions
     */
    protected function executeValidations(ModelInterface $model, array $validations)
    {
        $validationViolations = array();

        foreach ($validations as $validation) {
            list($validator, $method) = $validation;

            try {
                $validator->$method($model);
            } catch (ValidationException $e) {
                $validationViolations[] = $e;
            }
        }

        return $validationViolations;
    }

    /**
     * Check if the user is allowed to reach the step.
     *
     * @param Step $step
     * @throws AccessDeniedException
     */
    protected function checkCredentials(Step $step)
    {
        $roles = $step->getRoles();

        if (!empty($roles) && !$this->security->isGranted($roles)) {
            throw new AccessDeniedException($step->getName());
        }
    }
}