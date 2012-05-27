<?php

namespace FreeAgent\WorkflowBundle\Manager;

use Symfony\Component\DependencyInjection\Container;
use FreeAgent\WorkflowBundle\Model\ModelInterface;

class Manager
{
    protected $model;
    protected $workflow;
    protected $workflowName;
    protected $steps = array();
    protected $container;
    protected $canReachStep = array();

    /**
     * [__construct description]
     * @param Container $container [description]
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * [getDefaultStepName description]
     * @return string The default step name.
     */
    public function getDefaultStepName()
    {
        return $this->workflow['default_step'];
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

        return $this->getWorkflow();
    }

    /**
     * [getWorkflow description]
     * @return array The workflow.
     */
    public function getWorkflow()
    {
        return $this->workflow;
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
     * @return array The steps of the workflow.
     */
    public function getSteps()
    {
        return $this->workflow['steps'];
    }

    /**
     * [getStep description]
     * @param  string $stepName The name of the step.
     * @return array           The step.
     */
    public function getStep($stepName)
    {
        if (!array_key_exists($stepName, $this->workflow['steps'])) {
            throw new \Exception('Step with name "'.$stepName.'" is not in "'.$this->workflowName.'" workflow');
        }

        return $this->workflow['steps'][$stepName];
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

            if ($stepName != $this->getCurrentStepName())
            {
                $step        = $this->getStep($stepName);
                $currentStep = $this->getCurrentStep();

                if (array_key_exists('possible_next_steps', $currentStep)) {
                    if (in_array($stepName, $currentStep['possible_next_steps'])) {

                        if (!array_key_exists('validations', $step)) {
                            $this->canReachStep[$stepName] = true;
                        } else {
                            foreach ($step['validations'] as $validation) {
                                $validation = $this->getValidation($validation);

                                $this->canReachStep[$stepName] = false == $validation->validate($this->getModel()) ? false : true;
                            }
                        }
                    }
                }
            }
        }

        return $this->canReachStep[$stepName];
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
        $currentStep = $this->getCurrentStep();
        if (array_key_exists('actions', $currentStep)) {

            foreach ($currentStep['actions'] as $action) {
                $action = $this->getAction($action);

                if (false == $action->run($this->getModel())) {

                    return false;
                }
            }
        }

        return true;
    }
}
