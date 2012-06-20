<?php

namespace FreeAgent\WorkflowBundle\Handler;

use Symfony\Component\Security\Core\SecurityContext;

use FreeAgent\WorkflowBundle\Exception\AccessDeniedException;
use FreeAgent\WorkflowBundle\Exception\ValidationException;
use FreeAgent\WorkflowBundle\Flow\Step;
use FreeAgent\WorkflowBundle\Flow\Process;
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
     * @var Symfony\Component\Security\Core\SecurityContext
     */
    protected $security;

    /**
     * Construct.
     *
     * @param Process $process
     */
    public function __construct(Process $process, ModelStorage $storage, SecurityContext $security)
    {
        $this->process  = $process;
        $this->storage  = $storage;
        $this->security = $security;
    }

    /**
     * @see FreeAgent\WorkflowBundle\Handler.ProcessHandlerInterface::start()
     */
    public function start(ModelInterface $model)
    {
        // @todo: throw an exception here if model has already reached steps (for the current process)

        return $this->reachStep($model, $this->process->getStartStep());
    }

    /**
     * @see FreeAgent\WorkflowBundle\Handler.ProcessHandlerInterface::reachStep()
     */
    public function reachStep(ModelInterface $model, $stepName)
    {
        $step = $this->process->getStep($stepName);

        if (0 === count($this->executeStepValidations($model, $step))) {
            return $this->storage->newModelState($model, $this->process->getName(), $stepName, $step);
        }
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