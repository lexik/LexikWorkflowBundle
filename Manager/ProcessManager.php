<?php

namespace FreeAgent\WorkflowBundle\Manager;

use FreeAgent\WorkflowBundle\Exception\WorkflowException;

class ProcessManager
{
    /**
     * @var array
     */
    protected $processes;

    /**
     * Construct.
     *
     * @param array $processes
     */
    public function __construct(array $processes)
    {
        $this->processes = $processes;
    }

    /**
     * Returns a process by it name.
     *
     * @param string $name
     * @return FreeAgent\WorkflowBundle\Flow\Process
     *
     * @throws WorkflowException
     */
    public function getProcess($name)
    {
        if (!isset($this->processes[$name])) {
            throw new WorkflowException(sprintf('Unknown process "%s"', $name));
        }

        return $this->processes[$name];
    }
}