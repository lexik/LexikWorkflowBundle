<?php

namespace FreeAgent\WorkflowBundle\Flow;

use FreeAgent\WorkflowBundle\Model\ModelInterface;

/**
 * Define all required method a Process muste have.
 *
 */
interface ProcessInterface
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
     * @param string $step
     */
    public function reachStep(ModelInterface $model, $step);
}