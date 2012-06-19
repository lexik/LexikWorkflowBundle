<?php

namespace FreeAgent\WorkflowBundle\Tests\DependencyInjection;

use FreeAgent\WorkflowBundle\Tests\TestCase;
use FreeAgent\WorkflowBundle\DependencyInjection\FreeAgentWorkflowExtension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class FreeAgentWorkflowExtensionTest extends TestCase
{
    public function testLoad()
    {
        $extension = new FreeAgentWorkflowExtension();
        $extension->load(array($this->getSimpleConfig()), $container = new ContainerBuilder());

        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess') instanceof Definition);

        $extension = new FreeAgentWorkflowExtension();
        $extension->load(array($this->getConfig()), $container = new ContainerBuilder());

        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_create_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_validate_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_remove_doc') instanceof Definition);
    }
}
