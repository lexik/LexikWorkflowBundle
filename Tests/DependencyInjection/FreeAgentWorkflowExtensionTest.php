<?php

namespace FreeAgent\WorkflowBundle\Tests\DependencyInjection;

use FreeAgent\WorkflowBundle\Tests\TestCase;
use FreeAgent\WorkflowBundle\DependencyInjection\FreeAgentWorkflowExtension;
use FreeAgent\WorkflowBundle\Manager\ProcessManager;
use FreeAgent\WorkflowBundle\Flow\Process;
use FreeAgent\WorkflowBundle\Handler\ProcessHandler;

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

        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_create_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_validate_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_remove_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.handler.document_proccess') instanceof Definition);

        $processManager = $container->get('free_agent_workflow.process_manager');
        $this->assertTrue($processManager instanceof ProcessManager);
        $this->assertTrue($processManager->getProcess('document_proccess') instanceof Process);

        $processHandler = $container->get('free_agent_workflow.handler.document_proccess');
        $this->assertTrue($processHandler instanceof ProcessHandler);
    }
}
