<?php

namespace Lexik\Bundle\WorkflowBundle\Tests\Flow;

use Lexik\Bundle\WorkflowBundle\Tests\TestCase;
use Lexik\Bundle\WorkflowBundle\DependencyInjection\LexikWorkflowExtension;
use Lexik\Bundle\WorkflowBundle\Flow\Process;
use Lexik\Bundle\WorkflowBundle\Flow\Step;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProcessTest extends TestCase
{
    public function testProcessService()
    {
        $container = new ContainerBuilder();
        $container->set('next_state_condition', new \stdClass());

        $extension = new LexikWorkflowExtension();
        $extension->load(array($this->getConfig()), $container);

        $process = $container->get('lexik_workflow.process.document_proccess');
        $this->assertTrue($process instanceof Process);
        $this->assertTrue($process->getSteps() instanceof ArrayCollection);
        $this->assertEquals(3, $process->getSteps()->count());
        $this->assertTrue($process->getSteps()->get('step_create_doc') instanceof Step);
    }
}
