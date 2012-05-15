<?php

namespace FreeAgent\Bundle\WorkflowBundle\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use FreeAgent\Bundle\WorkflowBundle\Model\Example as ModelExample;

class ManagerTest extends WebTestCase
{
    private function getManager()
    {
        $client = static::createClient();

        $manager = $client->getContainer()->get('free_agent_workflow.manager');

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
