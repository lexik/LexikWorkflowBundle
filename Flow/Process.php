<?php

namespace FreeAgent\WorkflowBundle\Flow;

use FreeAgent\WorkflowBundle\Model\ModelInterface;

class Process implements NodeInterface, ProcessInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $steps;

    /**
     * Construct.
     *
     * @param string $name
     * @param string $steps
     */
    public function __construct($name, array $steps)
    {
        $this->name = $name;
        $this->steps = $steps;
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

    public function start(ModelInterface $model)
    {
        throw new \RuntimeException('TODO :p');
    }

    public function reachStep(ModelInterface $model, $step)
    {
        throw new \RuntimeException('TODO :p');
    }
}