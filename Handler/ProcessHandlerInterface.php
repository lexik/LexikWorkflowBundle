<?php

namespace FreeAgent\WorkflowBundle\Handler;

use FreeAgent\WorkflowBundle\Entity\ModelState;
use FreeAgent\WorkflowBundle\Model\ModelInterface;

interface ProcessHandlerInterface
{
    /**
     * Start the current process for the given model.
     *
     * @param ModelInterface $model
     * @return ModelState
     */
    public function start(ModelInterface $model);

    /**
     * Tries to reach a step with the given model.
     *
     * @param ModelInterface $model
     * @param string $stateName
     * @return ModelState
     */
    public function reachNextState(ModelInterface $model, $stateName);

    /**
     * Returns the current model state.
     *
     * @param ModelInterface $model
     * @return ModelState
     */
    public function getCurrentState(ModelInterface $model);

    /**
     * Returns true if the given model has completed the process.
     *
     * @param ModelInterface $model
     * @return boolean
     */
    public function isProcessComplete(ModelInterface $model);
}