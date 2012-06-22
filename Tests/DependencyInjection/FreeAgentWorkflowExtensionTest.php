<?php

namespace FreeAgent\WorkflowBundle\Tests\DependencyInjection;

use FreeAgent\WorkflowBundle\Tests\TestCase;
use FreeAgent\WorkflowBundle\DependencyInjection\FreeAgentWorkflowExtension;
use FreeAgent\WorkflowBundle\Flow\Process;
use FreeAgent\WorkflowBundle\Handler\ProcessHandlerFactory;
use FreeAgent\WorkflowBundle\Handler\ProcessHandler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class FreeAgentWorkflowExtensionTest extends TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder();

        // fake entity manager and security context services
        $container->set('doctrine.orm.entity_manager', $this->getMockSqliteEntityManager());
        $container->set('security.context', $this->getMockSecurityContext());

        $extension = new FreeAgentWorkflowExtension();
        $extension->load(array($this->getSimpleConfig()), $container);

        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess') instanceof Definition);

        $extension = new FreeAgentWorkflowExtension();
        $extension->load(array($this->getConfig()), $container);

        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_create_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_validate_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_remove_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.handler.document_proccess') instanceof Definition);

        $processHandlerFactory = $container->get('free_agent_workflow.process_handler_factory');
        $this->assertTrue($processHandlerFactory instanceof ProcessHandlerFactory);
        $this->assertTrue($processHandlerFactory->getProcess('document_proccess') instanceof Process);

        $processHandler = $container->get('free_agent_workflow.handler.document_proccess');
        $this->assertTrue($processHandler instanceof ProcessHandler);
    }
}
