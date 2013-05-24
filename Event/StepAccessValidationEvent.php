<?php

namespace Lexik\Bundle\WorkflowBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Lexik\Bundle\WorkflowBundle\Model\ModelInterface;
use Lexik\Bundle\WorkflowBundle\Flow\Step;
use Lexik\Bundle\WorkflowBundle\Validation\ViolationList;
use Lexik\Bundle\WorkflowBundle\Validation\Violation;

/**
 * Step access validation event.
 *
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 * @author Gilles Gauthier <g.gauthier@lexik.fr>
 */
class StepAccessValidationEvent extends Event
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
     * @var ViolationList
     */
    private $violationList;

    /**
     * Constructor.
     *
     * @param Step           $step
     * @param ModelInterface $model
     * @param ViolationList  $violationList
     */
    public function __construct(Step $step, ModelInterface $model, ViolationList $violationList)
    {
        $this->step          = $step;
        $this->model         = $model;
        $this->violationList = $violationList;
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
     * Returns the violation list.
     *
     * @return ViolationList
     */
    public function getViolationList()
    {
        return $this->violationList;
    }

    /**
     * Proxy method to add a violation.
     *
     * @param $message
     */
    public function addViolation($message)
    {
        $this->violationList->add(new Violation($message));
    }
}
