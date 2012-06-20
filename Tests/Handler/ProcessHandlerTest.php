<?php

namespace FreeAgent\WorkflowBundle\Tests\Handler;

use FreeAgent\WorkflowBundle\Tests\TestCase;
use FreeAgent\WorkflowBundle\Flow\Process;
use FreeAgent\WorkflowBundle\Flow\Step;
use FreeAgent\WorkflowBundle\Handler\ProcessHandler;
use FreeAgent\WorkflowBundle\Model\ModelStorage;
use FreeAgent\WorkflowBundle\Model\ModelInterface;
use FreeAgent\WorkflowBundle\Entity\ModelState;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SampleModel implements ModelInterface
{
    public $data = array();

    public function getWorkflowIdentifier()
    {
        return 'sample_identifier';
    }

    public function getWorkflowData()
    {
        return $this->data;
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
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var FreeAgent\WorkflowBundle\Model\ModelStorage
     */
    protected $modelStorage;

    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->getMockSqliteEntityManager();
        $this->createSchema($this->em);

        $this->modelStorage = new ModelStorage($this->em, 'FreeAgent\WorkflowBundle\Entity\ModelState');
    }

    public function testStart()
    {
        $model = new SampleModel();
        $modelState = $this->getProcessHandler()->start($model);

        $this->assertTrue($modelState instanceof ModelState);
        $this->assertEquals($model->getWorkflowIdentifier(), $modelState->getWorkflowIdentifier());
        $this->assertEquals('document_proccess', $modelState->getProcessName());
        $this->assertEquals('step_create_doc', $modelState->getStepName());
        $this->assertTrue($modelState->getReachedAt() instanceof \DateTime);
        $this->assertTrue(is_array($modelState->getData()));
        $this->assertEquals(0, count($modelState->getData()));
    }

    public function testStartWithData()
    {
        $data = array('some', 'informations');

        $model = new SampleModel();
        $model->data = $data;
        $modelState = $this->getProcessHandler()->start($model);

        $this->assertEquals($data, $modelState->getData());
    }

    /**
     * @expectedException        FreeAgent\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage The given model as already started this process.
     */
    public function testStartAlreadyStarted()
    {
        $model = new SampleModel();
        $this->modelStorage->newModelState($model, 'document_proccess', 'step_create_doc');

        $this->getProcessHandler()->start($model);
    }

    /**
     * @expectedException        FreeAgent\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage The given model has not started this process.
     */
    public function testReachNextStateNotStarted()
    {
        $model = new SampleModel();

        $this->getProcessHandler()->reachNextState($model, 'step_validate_doc');
    }

    public function testReachNextState()
    {
        $model = new SampleModel();
        $this->modelStorage->newModelState($model, 'document_proccess', 'step_create_doc');

        $modelState = $this->getProcessHandler()->reachNextState($model, 'step_validate_doc');

        $this->assertTrue($modelState instanceof ModelState);
        $this->assertEquals('step_validate_doc', $modelState->getStepName());
    }

    /**
     * @expectedException        FreeAgent\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage The step "step_create_doc" does not contain any next state named "step_fake".
     */
    public function testReachNextStateInvalidNextStep()
    {
        $model = new SampleModel();
        $this->modelStorage->newModelState($model, 'document_proccess', 'step_create_doc');

        $modelState = $this->getProcessHandler()->reachNextState($model, 'step_fake');
    }

    /**
     * @expectedException        FreeAgent\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage Can't find step named "step_unknow" in process "document_proccess".
     */
    public function testGetProcessStepInvalidStepName()
    {
        $reflectionClass = new \ReflectionClass('FreeAgent\WorkflowBundle\Handler\ProcessHandler');
        $method = $reflectionClass->getMethod('getProcessStep');
        $method->setAccessible(true);
        $method->invoke($this->getProcessHandler(), 'step_unknow');
    }

    protected function getProcessHandler()
    {
        $stepValidateDoc = new Step('step_validate_doc', 'Validate doc', array());
        $stepCreateDoc = new Step('step_create_doc', 'Create doc', array(
            'step_validate_doc' => array(
                'type'   => 'step',
                'target' => $stepValidateDoc,
            )
        ));
        $stepFake = new Step('step_fake', 'Fake', array());

        $process = new Process(
            'document_proccess',
            array(
                'step_create_doc'   => $stepCreateDoc,
                'step_validate_doc' => $stepValidateDoc,
                'step_fake'         => $stepFake,
            ),
            'step_create_doc',
            array('step_validate_doc')
        );

        $processHandler = new ProcessHandler($process, $this->modelStorage);
        $processHandler->setSecurityContext(new FakeSecurityContext());

        return $processHandler;
    }
}
