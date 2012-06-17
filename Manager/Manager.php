<?php

namespace FreeAgent\WorkflowBundle\Manager;

use Symfony\Component\DependencyInjection\Container;
use FreeAgent\WorkflowBundle\Model\ModelInterface;
use FreeAgent\WorkflowBundle\Step\Collection as StepCollection;
use FreeAgent\WorkflowBundle\Step\Step;
use FreeAgent\WorkflowBundle\Exception\ValidationException;
use FreeAgent\WorkflowBundle\Exception\WorkflowException;

class Manager
{
    protected $model;
    protected $workflow;
    protected $workflowName;
    protected $container;
    protected $canReachStep = array();
    protected $validationErrors = array();
    protected $defaultStep;
    protected $steps;
    protected $actions = array();
    protected $validations = array();

    /**
     * [__construct description]
     * @param Container $container [description]
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->steps = new StepCollection();
    }

    /**
     * [getDefaultStepName description]
     * @return string The default step name.
     */
    public function getDefaultStepName()
    {
        return $this->defaultStep->getName();
    }

    /**
     * [configureWorkflow description]
     * @param  string $workflowName The workflow name.
     * @return array The workflow.
     */
    public function configureWorkflow($workflowName)
    {
        $this->workflowName = $workflowName;

        $this->workflow = $this->container->getParameter('free_agent_workflow.workflows.'.$this->workflowName, null);

        if (is_null($this->workflow)) {
            throw new WorkflowException('The workflow "'.$this->workflowName.'" does not exist');
        }

        $defaultStep = $this->workflow['default_step'];
        if (!array_key_exists($defaultStep, $this->workflow['steps'])) {
            throw new WorkflowException('The default step of "'.$this->workflowName.'" does not exist');
        }

        foreach ($this->workflow['steps'] as $stepName => $stepConfiguration) {
            $this->steps->offsetSet($stepName, new Step($stepName, $stepConfiguration));
        }

        $this->defaultStep = new Step($defaultStep, $this->workflow['steps'][$defaultStep]);
        $this->actions     = array_key_exists('actions', $this->workflow) ? $this->workflow['actions'] : array();
        $this->validations = array_key_exists('validations', $this->workflow) ? $this->workflow['validations'] : array();

        return $this->getSteps();
    }

    /**
     * [setModel description]
     * @param ModelInterface $model The model subject of the workflow.
     */
    public function setModel(ModelInterface $model)
    {
        $this->model = $model;
        $this->configureWorkflow($this->model->getWorkflowName());
    }

    /**
     * [getModel description]
     * @return ModelInterface The model subject of the workflow.
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * [getSteps description]
     * @return StepCollection The workflow steps.
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * [getStep description]
     * @param  string $stepName The name of the step.
     * @return array           The step.
     */
    public function getStep($stepName)
    {
        if (!$this->getSteps()->offsetExists($stepName)) {
            throw new WorkflowException('Step with name "'.$stepName.'" is not in "'.$this->workflowName.'" workflow');
        }

        return $this->getSteps()->offsetGet($stepName);
    }

    /**
     * [getCurrentStep description]
     * @return array The current step.
     */
    public function getCurrentStep()
    {
        return $this->getStep($this->getCurrentStepName());
    }

    /**
     * [getCurrentStepName description]
     * @return string The current step name.
     */
    public function getCurrentStepName()
    {
        if ('' == $this->getModel()->getWorkflowStepName() || is_null($this->getModel()->getWorkflowStepName())) {
            $this->getModel()->setWorkflowStepName($this->getDefaultStepName());
        }

        return $this->getModel()->getWorkflowStepName();
    }

    /**
     * [reachStep description]
     * @param  string $stepName    The name of the step to reach.
     * @param  string $stepComment The comment link to the reach.
     * @param  string $stepAt      The date of the reach.
     * @return boolean             Return true on success false on failure.
     */
    public function reachStep($stepName, $stepComment = '', $stepAt = null)
    {
        if ($this->canReachStep($stepName)) {

            $this->getModel()->setWorkflowStepName($stepName);
            $this->getModel()->setWorkflowStepComment(trim($stepComment));
            $this->getModel()->setWorkflowStepAt(is_null($stepAt) ? time() : $stepAt);

            $this->runStepActions($stepName);
            $this->runActions();

            $this->canReachStep = array();

            return true;
        }

        return false;
    }

    /**
     * [canReachStep description]
     * @param  string $stepName The name of the step to reach.
     * @return [type]           [description]
     */
    public function canReachStep($stepName)
    {
        if (!array_key_exists($stepName, $this->canReachStep)) {

            $this->canReachStep[$stepName] = false;
            $this->validationErrors[$stepName] = array();

            if ($stepName != $this->getCurrentStepName()) {
                $stepToReach = $this->getStep($stepName);
                $currentStep = $this->getCurrentStep();

                if ($currentStep->hasPossibleNextStep($stepToReach->getName())) {

                    $preValidationResult = $this->preValidation($stepToReach->getName());

                    if ($preValidationResult) {
                        if (!$stepToReach->hasValidations()) {
                            $this->canReachStep[$stepToReach->getName()] = true;
                        } else {
                            foreach ($stepToReach->getValidations() as $validation) {
                                $validation = $this->getValidation($validation);

                                try {
                                    $validation->validate($this->getModel());
                                    $this->canReachStep[$stepToReach->getName()] = true;
                                } catch (ValidationException $e) {
                                    $this->validationErrors[$stepToReach->getName()][] = $e->getMessage();
                                    $this->canReachStep[$stepToReach->getName()] = false;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->canReachStep[$stepName];
    }

    public function preValidation($stepName)
    {
        $preValidationResult = true;
        if ($this->hasValidations()) {
            foreach ($this->getValidations() as $validation) {
                $validation = $this->getValidation($validation);

                try {
                    $validation->validate($this->getModel());
                } catch (ValidationException $e) {
                    $this->validationErrors[$stepName][] = $e->getMessage();
                    $preValidationResult = false;
                }
            }
        }

        return $preValidationResult;
    }

    public function getValidationErrors($stepName)
    {
        return (array_key_exists($stepName, $this->validationErrors)) ? $this->validationErrors[$stepName] : array();
    }

    public function getValidation($validation)
    {
        return $this->container->get($validation);
    }

    public function getValidations()
    {
        return $this->validations;
    }

    public function hasValidations()
    {
        return (!empty($this->validations));
    }

    public function getAction($action)
    {
        return $this->container->get($action);
    }

    public function getActions()
    {
        return $this->actions;
    }

    /**
     * [runStepActions description]
     * @return [type] [description]
     */
    public function runStepActions()
    {
        $step = $this->getCurrentStep();

        foreach ($step->getActions() as $action) {
            $action = $this->getAction($action);

            if (false == $action->run($this->getModel())) {

                return false;
            }
        }

        return true;
    }

    public function runActions()
    {
        foreach ($this->getActions() as $action) {
            $action = $this->getAction($action);

            if (false == $action->run($this->getModel())) {

                return false;
            }
        }

        return true;
    }
}
