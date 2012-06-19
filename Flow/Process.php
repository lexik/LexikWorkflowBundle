<?php

namespace FreeAgent\WorkflowBundle\Flow;

use FreeAgent\WorkflowBundle\Model\ModelInterface;

use Doctrine\Common\Collections\ArrayCollection;

class Process implements NodeInterface, ProcessInterface
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
     * Construct.
     *
     * @param string $name
     * @param array $steps
     */
    public function __construct($name, array $steps)
    {
        $this->name  = $name;
        $this->steps = new ArrayCollection($steps);
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
     * Get process steps
     *
     * @return ArrayCollection
     */
    public function getSteps()
    {
        return $this->steps;
    }

    public function start(ModelInterface $model)
    {
        throw new \RuntimeException('TODO :p');
    }

    public function reachStep(ModelInterface $model, $step)
    {
        throw new \RuntimeException('TODO :p');
    }
}