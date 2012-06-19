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

    public function getActions()
    {
        return $this->actions;
    }

    public function getValidations()
    {
        return $this->validations;
    }

    public function getNextSteps()
    {
        return $this->nextSteps;
    }

    public function hasValidations()
    {
        return !empty($this->validations);
    }

    public function hasActions()
    {
        return !empty($this->actions);
    }

    public function hasNextStep($stepName)
    {
        return in_array($stepName, $this->getNextSteps());
    }
}
