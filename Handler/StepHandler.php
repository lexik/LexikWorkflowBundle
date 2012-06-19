<?php

namespace FreeAgent\WorkflowBundle\Handler;

use Doctrine\Common\Collections\ArrayCollection;

class StepHandler
{
    /**
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $steps;

    /**
     * Construct.
     *
     * @param ArrayCollection $steps
     */
    public function __construct(ArrayCollection $steps)
    {
        $this->steps = $steps;
    }

    /**
     * Returns a step.
     *
     * @param string $name
     * @return FreeAgent\WorkflowBundle\Flow_Step
     */
    public function getStep($name)
    {
        return $this->steps->get($name);
    }

    /**
     * Returns true if the handler contains the step.
     *
     * @param string $name
     * @return boolean
     */
    public function hasStep($name)
    {
        return $this->steps->containsKey($name);
    }
}