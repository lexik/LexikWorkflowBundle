<?php

namespace Lexik\Bundle\WorkflowBundle\Flow;

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
     * Construct.
     *
     * @param string $name
     * @param string $targetType
     * @param Node   $target
     */
    public function __construct($name, $targetType, Node $target)
    {
        $this->name = $name;
        $this->targetType = $targetType;
        $this->target = $target;
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
}
