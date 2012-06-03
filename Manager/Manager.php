<?php

namespace FreeAgent\WorkflowBundle\Manager;

use Symfony\Component\DependencyInjection\Container;
use FreeAgent\WorkflowBundle\Model\ModelInterface;
use FreeAgent\WorkflowBundle\Step\Collection as StepCollection;
use FreeAgent\WorkflowBundle\Step\Step;

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
        return $this->defaultStep;
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
            throw new \Exception('The workflow "'.$this->workflowName.'" does not exist');
        }

        $defaultStep = $this->workflow['default_step'];
        if (!array_key_exists($defaultStep, $this->workflow['steps'])) {
            throw new \Exception('The default step of "'.$this->workflowName.'" does not exist');
        }

        foreach ($this->workflow['steps'] as $stepName => $stepConfiguration) {
            $this->steps->add($stepName, new Step($stepName, $stepConfiguration));
        }

        $this->defaultStep = new Step($defaultStep, $this->workflow['steps'][$defaultStep]);

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
            throw new \Exception('Step with name "'.$stepName.'" is not in "'.$this->workflowName.'" workflow');
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
        if ($this->canReachStep($stepName)){

            $this->getModel()->setWorkflowStepName($stepName);
            $this->getModel()->setWorkflowStepComment(trim($stepComment));
            $this->getModel()->setWorkflowStepAt(is_null($stepAt) ? time() : $stepAt);

            $this->runStepActions($stepName);

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
                $step        = $this->getStep($stepName);
                $currentStep = $this->getCurrentStep();

                if ($step->hasPossibleNextStep($stepName)) {

                    if (!$step->hasValidations()) {
                        $this->canReachStep[$stepName] = true;
                    } else {
                        foreach ($step->getValidations() as $validation) {
                            $validation = $this->getValidation($validation);

                            try {
                                $validation->validate($this->getModel());
                                $this->canReachStep[$stepName] = true;
                            } catch (\Exception $e) {
                                $this->validationErrors[$stepName][] = $e->getMessage();
                                $this->canReachStep[$stepName] = false;
                            }
                        }
                    }
                }
            }
        }

        return $this->canReachStep[$stepName];
    }

    public function getValidationErrors($stepName)
    {
        return (array_key_exists($stepName, $this->validationErrors)) ? $this->validationErrors[$stepName] : array();
    }

    public function getValidation($validation)
    {
        return $this->container->get($validation);
    }

    public function getAction($action)
    {
        return $this->container->get($action);
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
}
