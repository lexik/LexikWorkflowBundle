<?php

namespace FreeAgent\WorkflowBundle\Tests\DependencyInjection;

use FreeAgent\WorkflowBundle\DependencyInjection\FreeAgentWorkflowExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Definition;

class FreeAgentWorkflowExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder(new ParameterBag());
        $extension = new FreeAgentWorkflowExtension();

        $extension->load(array(array(
            'processes' => array(
                'document_proccess' => array(
                    'start' => null,
                    'steps' => array(),
                ),
            )
        )), $container);

        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess') instanceof Definition);

        $extension->load(array(array(
            'processes' => array(
                'document_proccess' => array(
                    'start' => 'step_create_doc',
                    'end'   => array('step_validate_doc', 'step_remove_doc'),
                    'steps' => array(
                        'step_create_doc'=> array(
                            'roles'       => array(),
                            'actions'     => array(),
                            'validations' => array(),
                            'next_steps'  => array(
                                'validate' => array(
                                    'target' => 'step_validate_doc',
                                ),
                                'remove' => array(
                                    'target' => 'step_remove_doc',
                                ),
                            ),
                        ),
                        'step_validate_doc' => array(),
                        'step_remove_doc' => array(),
                    ),
                ),
            )
        )), $container);

        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_create_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_validate_doc') instanceof Definition);
        $this->assertTrue($container->getDefinition('free_agent_workflow.process.document_proccess.step.step_remove_doc') instanceof Definition);
    }
}
