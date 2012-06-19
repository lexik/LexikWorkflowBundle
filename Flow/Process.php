<?php

namespace FreeAgent\WorkflowBundle\Flow;

use Doctrine\Common\Collections\ArrayCollection;

use FreeAgent\WorkflowBundle\Model\ModelInterface;

class Process implements NodeInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $startStep;

    /**
     * @var array
     */
    protected $endSteps;

    /**
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $steps;

    /**
     * Construct.
     *
     * @param string $name
     * @param string $steps
     */
    public function __construct($name, array $steps, $startStep, $endSteps)
    {
        $this->name = $name;
        $this->steps = new ArrayCollection($steps);
        $this->startStep = $startStep;
        $this->endSteps = $endSteps;
    }

    /**
     * Get process name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns a step by its name.
     *
     * @param string $stepName
     * @return FreeAgent\WorkflowBundle\Flow\Step
     */
    public function getStep($stepName)
    {
        return $this->steps->get($stepName);
    }
}