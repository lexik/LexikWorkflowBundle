<?php

namespace FreeAgent\WorkflowBundle\Flow;

class Process implements NodeInterface
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
    public function __construct($name, $steps)
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
}