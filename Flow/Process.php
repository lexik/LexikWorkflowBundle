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
     * @var string
     */
    protected $startStep;

    /**
     * @var array
     */
    protected $endSteps;

    /**
     * @var FreeAgent\WorkflowBundle\Handler\StepHandler
     */
    protected $stepHandler;

    /**
     * Construct.
     *
     * @param string $name
     * @param string $steps
     */
    public function __construct($name, array $steps, $startStep, $endSteps, $stepHandlerClass)
    {
        $this->name = $name;
        $this->startStep = $startStep;
        $this->endSteps = $endSteps;
        $this->stepHandler = new $stepHandlerClass($this->steps);
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
        return $this->reachStep($model, $this->startStep);
    }

    public function reachStep(ModelInterface $model, $stepName)
    {
        throw new \RuntimeException('TODO :p');
    }

    /**
     * Returns a step by its name.
     *
     * @param string $stepName
     * @return FreeAgent\WorkflowBundle\Flow\Step
     */
    public function getStep($stepName)
    {
        throw new \RuntimeException('TODO :p');
    }
}