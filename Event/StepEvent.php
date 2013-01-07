<?php

namespace FreeAgent\WorkflowBundle\Event;

use FreeAgent\WorkflowBundle\Entity\ModelState;
use FreeAgent\WorkflowBundle\Model\ModelInterface;
use FreeAgent\WorkflowBundle\Flow\Step;

/**
 * Step event.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class StepEvent
{
    /**
     * @var Step
     */
    private $step;

    /**
     * @var ModelInterface
     */
    private $model;

    /**
     * @var ModelState
     */
    private $modelState;

    /**
     * Construct.
     *
     * @param Step $step
     * @param ModelInterface $model
     * @param ModelState $modelState
     */
    public function __construct(Step $step, ModelInterface $model, ModelState $modelState)
    {
        $this->step = $step;
        $this->model = $model;
        $this->modelState = $modelState;
    }

    /**
     * Returns the reached step.
     *
     * @return Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Returns the model.
     *
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Returs the last model state.
     *
     * @return ModelState
     */
    public function getModelState()
    {
        return $this->modelState;
    }
}
