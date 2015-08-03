<?php

namespace Lexik\Bundle\WorkflowBundle\Tests\DependencyInjection;

use Lexik\Bundle\WorkflowBundle\Tests\TestCase;
use Lexik\Bundle\WorkflowBundle\DependencyInjection\LexikWorkflowExtension;
use Lexik\Bundle\WorkflowBundle\Flow\Process;
use Lexik\Bundle\WorkflowBundle\Handler\ProcessAggregator;
use Lexik\Bundle\WorkflowBundle\Handler\ProcessHandler;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LexikWorkflowExtensionTest extends TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder();

        // fake entity manager and security context services
        $container->set('doctrine.orm.entity_manager', $this->getSqliteEntityManager());
        $container->set('security.authorization_checker', $this->getMockAuthorizationChecker());
        $container->set('event_dispatcher', new EventDispatcher());
        $container->set('next_state_condition', new \stdClass());

        // simple config
        $extension = new LexikWorkflowExtension();
        $extension->load(array($this->getSimpleConfig()), $container);

        $this->assertTrue($container->getDefinition('lexik_workflow.process.document_proccess') instanceof Definition);

        // config with a process
        $extension = new LexikWorkflowExtension();
        $extension->load(array($this->getConfig()), $container);

        $this->assertTrue($container->getDefinition('lexik_workflow.process.document_proccess') instanceof Definition);
        $this->assertTrue($container->getDefinition('lexik_workflow.process.document_proccess.step.step_create_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('lexik_workflow.process.document_proccess.step.step_validate_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('lexik_workflow.process.document_proccess.step.step_remove_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('lexik_workflow.handler.document_proccess') instanceof Definition);

        $processHandlerFactory = $container->get('lexik_workflow.process_aggregator');
        $this->assertTrue($processHandlerFactory instanceof ProcessAggregator);
        $this->assertTrue($processHandlerFactory->getProcess('document_proccess') instanceof Process);

        $processHandler = $container->get('lexik_workflow.handler.document_proccess');
        $this->assertTrue($processHandler instanceof ProcessHandler);
    }
}
