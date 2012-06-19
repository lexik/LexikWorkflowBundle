<?php

namespace FreeAgent\WorkflowBundle\Tests\Flow;

use FreeAgent\WorkflowBundle\Tests\TestCase;
use FreeAgent\WorkflowBundle\DependencyInjection\FreeAgentWorkflowExtension;
use FreeAgent\WorkflowBundle\Flow\Process;
use FreeAgent\WorkflowBundle\Flow\Step;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProcessTest extends TestCase
{
    public function testProcessService()
    {
        $extension = new FreeAgentWorkflowExtension();
        $extension->load(array($this->getConfig()), $container = new ContainerBuilder());

        $process = $container->get('free_agent_workflow.process.document_proccess');
        $this->assertTrue($process instanceof Process);
        $this->assertTrue($process->getSteps() instanceof ArrayCollection);
        $this->assertEquals(3, $process->getSteps()->count());
        $this->assertTrue($process->getSteps()->get('step_create_doc') instanceof Step);
    }
}
