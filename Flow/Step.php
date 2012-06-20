<?php

namespace FreeAgent\WorkflowBundle\Flow;

class Step implements NodeInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var array
     */
    protected $actions;

    /**
     * @var array
     */
    protected $validations;

    /**
     * @var array
     */
    protected $nextSteps;

    /**
     * Construct.
     *
     * @param string $name
     * @param string $label
     * @param array $nextSteps
     * @param array $validations
     * @param array $actions
     * @param array $roles
     */
    public function __construct($name, $label, array $nextSteps, array $validations = null, array $actions = null, array $roles = null)
    {
        $this->name = $name;
        $this->label = $label;

        $this->nextSteps = $nextSteps;
        $this->validations = (null != $validations) ? $validations : array();
        $this->actions = (null != $actions) ? $actions : array();
        $this->roles = (null != $roles) ? $roles : array();
    }

    /**
     * Get step name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get step label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns all actions to execute one the step is reached.
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Return all validations to execute the check the step is reachable.
     *
     * @return array
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * Returns all next steps.
     *
     * @return array
     */
    public function getNextSteps()
    {
        return $this->nextSteps;
    }

    /**
     * Returns true if the step requires some validations to be reached.
     *
     * @return boolean
     */
    public function hasValidations()
    {
        return !empty($this->validations);
    }

    /**
     * Returns true if the step has some actions to execute once it reached.
     *
     * @return boolean
     */
    public function hasActions()
    {
        return !empty($this->actions);
    }

    /**
     * Returns true if the given step name is one of the next steps.
     *
     * @param string $stepName
     * @return boolean
     */
    public function hasNextStep($stepName)
    {
        return in_array($stepName, $this->getNextSteps());
    }

    /**
     * Returns required roles to reach the step.
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
