<?php

namespace FreeAgent\WorkflowBundle\Tests\Manager;

use Symfony\Component\DependencyInjection\Container;
use FreeAgent\WorkflowBundle\Manager\Manager;
use FreeAgent\WorkflowBundle\Model\ModelInterface;
use FreeAgent\WorkflowBundle\Action\ActionInterface;
use FreeAgent\WorkflowBundle\Validation\ValidationInterface;

class ModelExample implements ModelInterface
{
    private $workflow_name = 'example';
    private $workflow_step_name;
    private $workflow_step_comment;

    public function getWorkflowName()
    {
        return $this->workflow_name;
    }

    public function setWorkflowName($workflowName)
    {
        return $this->workflow_name = $workflowName;
    }

    public function setWorkflowStepName($stepName)
    {
        $this->workflow_step_name = $stepName;
    }

    public function getWorkflowStepName()
    {
        return $this->workflow_step_name;
    }

    public function setWorkflowStepComment($stepComment)
    {
        $this->workflow_step_comment = $stepComment;
    }

    public function getWorkflowStepComment()
    {
        return $this->workflow_step_comment;
    }
}

class ActionExample implements ActionInterface
{
    public function run($model)
    {
        return true;
    }
}

class ValidationExample implements ValidationInterface
{
    public function validate($model)
    {
        return true;
    }
}

class ManagerForTest extends Manager
{
    public function configureWorkflow($workflowName)
    {
        $this->workflow = array(
            'default_step' => 'draft',
            'steps' => array(
                'draft' => array(
                    'label' => 'Draft',
                    'actions' => array(
                        'free_agent_workflow.action.example',
                    ),
                    'validations' => array(
                        'free_agent_workflow.validation.example',
                        'free_agent_workflow.validation.example',
                    ),
                    'possible_next_steps' => array(
                        'removed',
                        'validated',
                    ),
                ),
                'removed' => array(
                    'label' => 'Removed',
                    'actions' => array(
                        'free_agent_workflow.action.example',
                    ),
                    'validations' => array(
                        'free_agent_workflow.validation.example',
                        'free_agent_workflow.validation.example',
                    ),
                    'possible_next_steps' => array(
                        'draft',
                    ),
                ),
                'validated' => array(
                    'label' => 'Validated',
                    'actions' => array(
                        'free_agent_workflow.action.example',
                    ),
                    'validations' => array(
                        'free_agent_workflow.validation.example',
                    ),
                    'possible_next_steps' => array(
                        'published',
                        'removed',
                        'draft',
                    ),
                ),
                'published' => array(
                    'label' => 'Published',
                    'actions' => array(
                        'free_agent_workflow.action.example',
                    ),
                    'validations' => array(
                        'free_agent_workflow.validation.example',
                        'free_agent_workflow.validation.example',
                    ),
                    'possible_next_steps' => array(
                        'unpublished',
                        'removed',
                        'draft',
                    ),
                ),
                'unpublished' => array(
                    'label' => 'Unpublished',
                    'actions' => array(
                        'free_agent_workflow.action.example',
                    ),
                    'validations' => array(
                        'free_agent_workflow.validation.example',
                        'free_agent_workflow.validation.example',
                    ),
                    'possible_next_steps' => array(
                        'published',
                        'removed',
                        'draft',
                    ),
                ),
            ),
        );

        return $this->getWorkflow();
    }

    public function getValidation($validation)
    {
        return new ValidationExample();
    }

    public function getAction($action)
    {
        return new ActionExample();
    }
}

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    private function getManager()
    {
        $manager = new ManagerForTest(new Container());

        $model = new ModelExample();

        $manager->setModel($model);

        $manager->configureWorkflow($model->getWorkflowName());

        $workflowDefaultStepName = $manager->getDefaultStepName();

        $manager->getModel()->setWorkflowStepName($workflowDefaultStepName);

        return $manager;
    }

    public function testWorkflowCurrentStepName()
    {
        $manager = $this->getManager();

        $workflowDefaultStepName = $manager->getDefaultStepName();
        $workflowCurrentStepName = $manager->getCurrentStepName();

        $this->assertEquals($workflowDefaultStepName, $workflowCurrentStepName);
    }

    public function testCanReachStep()
    {
        $manager = $this->getManager();

        // Already at step
        $canReachStep = $manager->canReachStep('draft');

        $this->assertEquals(false, $canReachStep);

        // Step not reachable
        $canReachStep = $manager->canReachStep('published');

        $this->assertEquals(false, $canReachStep);

        // Step reachable
        $canReachStep = $manager->canReachStep('removed');

        $this->assertEquals(true, $canReachStep);
    }
}
