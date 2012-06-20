<?php

namespace FreeAgent\WorkflowBundle\Tests\Handler;

use FreeAgent\WorkflowBundle\Tests\TestCase;
use FreeAgent\WorkflowBundle\Flow\Process;
use FreeAgent\WorkflowBundle\Flow\Step;
use FreeAgent\WorkflowBundle\Handler\ProcessHandler;
use FreeAgent\WorkflowBundle\Model\ModelStorage;
use FreeAgent\WorkflowBundle\Model\ModelInterface;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SampleModel implements ModelInterface
{
    public function getWorkflowIdentifier()
    {
        return 'sample_hash';
    }
}

class FakeSecurityContext implements SecurityContextInterface
{
    public function getToken() {}

    public function setToken(TokenInterface $token = null) {}

    public function isGranted($attributes, $object = null)
    {
        return true;
    }
}

class ProcessHandlerTest extends TestCase
{
    public function testStart()
    {
        $processHandler = $this->getProcessHandler();

        $model = new SampleModel();

        $processHandler->start($model);
    }

    protected function getProcessHandler()
    {
        $stepValidateDoc = new Step('step_validate_doc', 'Validate doc', array());
        $stepCreateDoc   = new Step('step_create_doc', 'Create doc', array(
            'step_validate_doc' => array(
                'type'   => 'step',
                'target' => $stepValidateDoc,
            )
        ));

        $process = new Process(
            'document_proccess',
            array(
                'step_create_doc'   => $stepCreateDoc,
                'step_validate_doc' => $stepValidateDoc,
            ),
            'step_create_doc',
            array('step_validate_doc')
        );

        $em = $this->getMockSqliteEntityManager();
        $this->createSchema($em);

        $modelStorage = new ModelStorage($em, 'FreeAgent\WorkflowBundle\Entity\ModelState');

        $processHandler = new ProcessHandler($process, $modelStorage);
        $processHandler->setSecurityContext(new FakeSecurityContext());

        return $processHandler;
    }
}
