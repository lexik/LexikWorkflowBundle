<?php

namespace Lexik\Bundle\WorkflowBundle\Model;

use Lexik\Bundle\WorkflowBundle\Entity\ModelState;

interface ModelStateInterface
{
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
