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
 *
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
     * @param Process $process
     */
    public function __construct(Process $process, ModelStorage $storage)
    {
        $this->process  = $process;
        $this->storage  = $storage;
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

        $step = $currentStep->getNextStateTarget($stateName);

        return $this->reachStep($model, $step);
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

        $errors = $this->executeStepValidations($model, $step);

        if (0 === count($errors)) {
            $modelState = $this->storage->newModelStateSuccess($model, $this->process->getName(), $step->getName());

            // @todo run actions

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
     * Execute validations of a given step.
     *
     * @param ModelInterface $model
     * @param Step           $step
     *
     * @return array An array of validation exceptions
     */
    protected function executeStepValidations(ModelInterface $model, Step $step)
    {
        $validationViolations = array();

        foreach ($step->getValidations() as $validation) {
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
        if (!$this->security->isGranted($step->getRoles())) {
            throw new AccessDeniedException($step->getName());
        }
    }
}