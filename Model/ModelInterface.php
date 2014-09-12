<?php

namespace Lexik\Bundle\WorkflowBundle\Model;

use Lexik\Bundle\WorkflowBundle\Entity\ModelState;

interface ModelInterface
{
    /**
     * Returns a unique identifier.
     *
     * @return mixed
     */
    public function getWorkflowIdentifier();

    /**
     * Returns data to store in the ModelState.
     *
     * @return array
     */
    public function getWorkflowData();

    /**
     * Add modelState
     *
     * @param ModelState $modelState
     */
    public function addState(ModelState $modelState);

    /**
     * Get states
     *
     * @return array
     */
    public function getStates();
}
