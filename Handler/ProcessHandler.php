<?php

namespace FreeAgent\WorkflowBundle\Handler;

use FreeAgent\WorkflowBundle\Flow\Process;
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
     * Construct.
     *
     * @param Process $process
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
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
}