<?php

namespace FreeAgent\WorkflowBundle\Handler;

use FreeAgent\WorkflowBundle\Model\ModelInterface;

interface ProcessHandlerInterface
{
    /**
     * Start the current process for the given model.
     *
     * @param ModelInterface $model
     */
    public function start(ModelInterface $model);

    /**
     * Tries to reach a step with the given model.
     *
     * @param ModelInterface $model
     * @param string $stateName
     */
    public function reachNextState(ModelInterface $model, $stateName);
}