<?php

namespace FreeAgent\WorkflowBundle\Flow;

interface NodeInterface
{
    /**
     * Return the node name.
     *
     * @return string
     */
    public function getName();
}