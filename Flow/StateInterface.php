<?php

namespace FreeAgent\WorkflowBundle\Flow;

interface StateInterface
{
    const TYPE_STEP    = 'step';
    const TYPE_PROCESS = 'process';

    /**
     * Returns the state type.
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the state name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the state target.
     *
     * @return NodeInterface
     */
    public function getTarget();

    /**
     * Returns additional validation to execute before reaching the state target.
     *
     * @return array
     */
    public function getAdditionalValidations();
}