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
        $this->actions = array_key_exists('actions', $configuration) ? $configuration['actions'] = array();
        $this->validations = array_key_exists('validations', $configuration) ? $configuration['validations'] = array();
        $this->possible_next_steps = array_key_exists('possible_next_steps', $configuration) ? $configuration['possible_next_steps'] = array();
    }

    public function getName($name)
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
        return !empty($this->getValidations());
    }

    public function hasActions()
    {
        return !empty($this->getActions());
    }

    public function hasPossibleNextStep($stepName)
    {
        return array_key_exists($stepName, $this->getPossibleNextSteps());
    }
}
