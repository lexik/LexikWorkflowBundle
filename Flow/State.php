<?php

namespace FreeAgent\WorkflowBundle\Flow;

/**
 * A State represent one of the available next element (step) a given step.
 *
 */
class State implements StateInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Step
     */
    protected $target;

    /**
     * @var array
     */
    protected $validations;

    /**
     * Construct.
     *
     * @param string $name
     * @param string $type
     * @param Step $target
     * @param array $validations
     */
    public function __construct($name, $type, $target, array $validations = array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->target = $target;
        $this->validations = $validations;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalValidations()
    {
        return $this->validations;
    }
}