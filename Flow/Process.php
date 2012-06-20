<?php

namespace FreeAgent\WorkflowBundle\Flow;

use FreeAgent\WorkflowBundle\Model\ModelInterface;

use Doctrine\Common\Collections\ArrayCollection;

class Process implements NodeInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ArrayCollection
     */
    protected $steps;

    /**
     * @var string
     */
    protected $startStep;

    /**
     * @var array
     */
    protected $endSteps;

    /**
     * Construct.
     *
     * @param string $name
     * @param array  $steps
     * @param string $startStep
     * @param array  $endSteps
     */
    public function __construct($name, array $steps, $startStep, $endSteps)
    {
        $this->name      = $name;
        $this->steps     = new ArrayCollection($steps);
        $this->startStep = $startStep;
        $this->endSteps  = $endSteps;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Get process name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get process steps.
     *
     * @return ArrayCollection
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Returns a step by its name.
     *
     * @param string $stepName
     *
     * @return FreeAgent\WorkflowBundle\Flow\Step
     */
    public function getStep($stepName)
    {
        return $this->steps->get($stepName);
    }

    /**
     * Returns the first step.
     *
     * @return FreeAgent\WorkflowBundle\Flow\Step
     */
    public function getStartStep()
    {
        return $this->startStep;
    }
}