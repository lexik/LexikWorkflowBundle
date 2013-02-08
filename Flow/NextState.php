<?php

namespace FreeAgent\WorkflowBundle\Flow;

/**
 * A State represent one of the available next element (step) a given step.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class NextState implements NextStateInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $targetType;

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
     * @param string        $name
     * @param string        $targetType
     * @param NodeInterface $target
     * @param array         $validations
     */
    public function __construct($name, $targetType, NodeInterface $target, array $validations = array())
    {
        $this->name = $name;
        $this->targetType = $targetType;
        $this->target = $target;
        $this->validations = $validations;
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
    public function getTargetType()
    {
        return $this->type;
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
