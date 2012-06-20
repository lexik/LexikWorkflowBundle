<?php

namespace FreeAgent\WorkflowBundle\Model;

interface ModelInterface
{
    /**
     * Returns a unique identifier.
     *
     * @return mixed
     */
    public function getWorkflowIdentifier();
}
