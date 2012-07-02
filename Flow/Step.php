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
    protected $nextStates;

    /**
     * @var string
     */
    protected $onInvalid;

    /**
     * Construct.
     *
     * @param string $name
     * @param string $label
     * @param array $nextStates
     * @param array $validations
     * @param array $actions
     * @param array $roles
     * @param string $onInvalid
     */
    public function __construct($name, $label, array $nextStates = array(), array $validations = array(), array $actions = array(), array $roles = array(), $onInvalid = null)
    {
        $this->name        = $name;
        $this->label       = $label;
        $this->nextStates  = $nextStates;
        $this->validations = $validations;
        $this->actions     = $actions;
        $this->roles       = $roles;
        $this->onInvalid   = $onInvalid;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
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
     * Returns all next steps.
     *
     * @return array
     */
    public function getNextStates()
    {
        return $this->nextStates;
    }

    /**
     * Returns true if the given step name is one of the next steps.
     *
     * @param string $stepName
     * @return boolean
     */
    public function hasNextState($stateName)
    {
        return in_array($stateName, array_keys($this->nextStates));
    }

    /**
     * Returns the target of the given state.
     *
     * @param string $stateName
     * @return FreeAgent\WorkflowBundle\Flow\State
     */
    public function getNextState($name)
    {
        return $this->nextStates[$name];
    }

    /**
     * Create and add a new next state.
     *
     * @param string $name
     * @param string $type
     * @param Step $target
     * @param array $validations
     */
    public function addNextState($name, $type, $target, array $validations = array())
    {
        $this->nextStates[$name] = new State($name, $type, $target, $validations);
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
     * Returns true if the step requires some validations to be reached.
     *
     * @return boolean
     */
    public function hasValidations()
    {
        return !empty($this->validations);
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
     * Returns true if the step has some actions to execute once it reached.
     *
     * @return boolean
     */
    public function hasActions()
    {
        return !empty($this->actions);
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

    /**
     * Returns the step to reach if validation fail to reach this step.
     *
     * @return string
     */
    public function getOnInvalid()
    {
        return $this->onInvalid;
    }
}
