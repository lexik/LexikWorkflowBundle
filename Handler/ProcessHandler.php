<?php

namespace FreeAgent\WorkflowBundle\Handler;

use Symfony\Component\Security\Core\SecurityContext;

use FreeAgent\WorkflowBundle\Exception\AccessDeniedException;
use FreeAgent\WorkflowBundle\Flow\Step;
use FreeAgent\WorkflowBundle\Flow\Process;
use FreeAgent\WorkflowBundle\Model\ModelStorage;
use FreeAgent\WorkflowBundle\Model\ModelInterface;

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
        throw new \RuntimeException('TODO :p');
    }

    /**
     * @see FreeAgent\WorkflowBundle\Handler.ProcessHandlerInterface::reachStep()
     */
    public function reachStep(ModelInterface $model, $stepName)
    {
        throw new \RuntimeException('TODO :p');
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