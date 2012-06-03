<?php

namespace FreeAgent\WorkflowBundle\Step;

class Step
{
    protected $name;
    protected $actions = array();
    protected $validations = array();
    protected $possible_next_steps = array();

    public function __construct($name, $configuration = array())
    {
        $this->name = $name;
        $this->actions = array_key_exists('actions', $configuration) ? $configuration['actions'] : array();
        $this->validations = array_key_exists('validations', $configuration) ? $configuration['validations'] : array();
        $this->possible_next_steps = array_key_exists('possible_next_steps', $configuration) ? $configuration['possible_next_steps'] : array();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function getValidations()
    {
        return $this->validations;
    }

    public function getPossibleNextSteps()
    {
        return $this->possible_next_steps;
    }

    public function hasValidations()
    {
        return !empty($this->validations);
    }

    public function hasActions()
    {
        return !empty($this->actions);
    }

    public function hasPossibleNextStep($stepName)
    {
        return in_array($stepName, $this->getPossibleNextSteps());
    }
}
