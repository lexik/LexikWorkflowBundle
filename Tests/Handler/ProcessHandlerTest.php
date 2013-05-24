<?php

namespace Lexik\Bundle\WorkflowBundle\Tests\Handler;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Lexik\Bundle\WorkflowBundle\Flow\NextStateInterface;
use Lexik\Bundle\WorkflowBundle\Flow\Process;
use Lexik\Bundle\WorkflowBundle\Flow\Step;
use Lexik\Bundle\WorkflowBundle\Handler\ProcessHandler;
use Lexik\Bundle\WorkflowBundle\Model\ModelStorage;
use Lexik\Bundle\WorkflowBundle\Entity\ModelState;
use Lexik\Bundle\WorkflowBundle\Tests\TestCase;
use Lexik\Bundle\WorkflowBundle\Tests\Fixtures\FakeProcessListener;
Use Lexik\Bundle\WorkflowBundle\Tests\Fixtures\FakeModel;
use Lexik\Bundle\WorkflowBundle\Tests\Fixtures\FakeSecurityContext;
use Lexik\Bundle\WorkflowBundle\Tests\Fixtures\FakeValidatorListener;
use Lexik\Bundle\WorkflowBundle\Tests\Fixtures\FakeAction;

class ProcessHandlerTest extends TestCase
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var Lexik\Bundle\WorkflowBundle\Model\ModelStorage
     */
    protected $modelStorage;

    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->getMockSqliteEntityManager();
        $this->createSchema($this->em);

        $this->modelStorage = new ModelStorage($this->em, 'Lexik\Bundle\WorkflowBundle\Entity\ModelState');
    }

    public function testStart()
    {
        $model = new FakeModel();
        $modelState = $this->getProcessHandler()->start($model);

        $this->assertTrue($modelState instanceof ModelState);
        $this->assertEquals($model->getWorkflowIdentifier(), $modelState->getWorkflowIdentifier());
        $this->assertEquals('document_proccess', $modelState->getProcessName());
        $this->assertEquals('step_create_doc', $modelState->getStepName());
        $this->assertTrue($modelState->getCreatedAt() instanceof \DateTime);
        $this->assertTrue(is_array($modelState->getData()));
        $this->assertEquals(0, count($modelState->getData()));
        $this->assertEquals(FakeModel::STATUS_CREATE, $model->getStatus());
    }

    public function testStartWithData()
    {
        $data = array('some', 'informations');

        $model = new FakeModel();
        $model->data = $data;
        $modelState = $this->getProcessHandler()->start($model);

        $this->assertEquals($data, $modelState->getData());
    }

    /**
     * @expectedException        Lexik\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage The given model has already started the "document_proccess" process.
     */
    public function testStartAlreadyStarted()
    {
        $model = new FakeModel();
        $this->modelStorage->newModelStateSuccess($model, 'document_proccess', 'step_create_doc');

        $this->getProcessHandler()->start($model);
    }

    /**
     * @expectedException        Lexik\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage The given model has not started the "document_proccess" process.
     */
    public function testReachNextStateNotStarted()
    {
        $model = new FakeModel();

        $this->getProcessHandler()->reachNextState($model, 'validate');
    }

    public function testReachNextState()
    {
        $model = new FakeModel();
        $previous = $this->modelStorage->newModelStateSuccess($model, 'document_proccess', 'step_create_doc');

        $modelState = $this->getProcessHandler()->reachNextState($model, 'validate');

        $this->assertTrue($modelState instanceof ModelState);
        $this->assertEquals('step_validate_doc', $modelState->getStepName());
        $this->assertTrue($modelState->getSuccessful());
        $this->assertTrue($modelState->getPrevious() instanceof ModelState);
        $this->assertEquals($previous->getId(), $modelState->getPrevious()->getId());
        $this->assertEquals(FakeModel::STATUS_VALIDATE, $model->getStatus());
    }

    /**
     * @expectedException        Lexik\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage The step "step_create_doc" does not contain any next state named "step_fake".
     */
    public function testReachNextStateInvalidNextStep()
    {
        $model = new FakeModel();
        $this->modelStorage->newModelStateSuccess($model, 'document_proccess', 'step_create_doc');

        $modelState = $this->getProcessHandler()->reachNextState($model, 'step_fake');
    }

    public function testReachNextStateWithListener()
    {
        $this->assertEquals(0, FakeProcessListener::$call);

        $reflectionClass = new \ReflectionClass('Lexik\Bundle\WorkflowBundle\Handler\ProcessHandler');
        $method = $reflectionClass->getMethod('reachStep');
        $method->setAccessible(true);
        $method->invoke($this->getProcessHandler(), new FakeModel(), new Step('step_fake', 'Fake'));

        $this->assertEquals(1, FakeProcessListener::$call);
    }

    public function testReachNextStateOnInvalid()
    {
        $model = new FakeModel();
        $this->modelStorage->newModelStateSuccess($model, 'document_proccess', 'step_create_doc');

        $modelState = $this->getProcessHandler()->reachNextState($model, 'remove_on_invalid');

        $this->assertEquals('step_fake', $modelState->getStepName());
    }

    public function testExecuteValidations()
    {
        $model = new FakeModel();
        $this->modelStorage->newModelStateSuccess($model, 'document_proccess', 'step_create_doc');

        $modelState = $this->getProcessHandler()->reachNextState($model, 'remove');

        $this->assertFalse($modelState->getSuccessful());
        $this->assertEquals(array('Validation error!'), $modelState->getErrors());
    }

    /**
     * @expectedException        Lexik\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage Can't find step named "step_unknow" in process "document_proccess".
     */
    public function testGetProcessStepInvalidStepName()
    {
        $reflectionClass = new \ReflectionClass('Lexik\Bundle\WorkflowBundle\Handler\ProcessHandler');
        $method = $reflectionClass->getMethod('getProcessStep');
        $method->setAccessible(true);
        $method->invoke($this->getProcessHandler(), 'step_unknow');
    }

    public function testExecutePreValidations()
    {
        // reset fake calls
        FakeProcessListener::$call = 0;

        $model = new FakeModel();
        $this->modelStorage->newModelStateSuccess($model, 'document_proccess', 'step_create_doc');
        $modelState = $this->getProcessHandler()->reachNextState($model, 'validate_with_pre_validation');

        $this->assertTrue($modelState->getSuccessful());
        $this->assertEquals('step_validate_doc', $modelState->getStepName());

        $this->assertEquals(0, FakeProcessListener::$call);

        $model = new FakeModel();
        $this->modelStorage->newModelStateSuccess($model, 'document_proccess', 'step_create_doc');
        $modelState = $this->getProcessHandler()->reachNextState($model, 'validate_with_pre_validation_invalid');

        $this->assertFalse($modelState->getSuccessful());
        $this->assertEquals(array('Validation error!'), $modelState->getErrors());

        $this->assertEquals(1, FakeProcessListener::$call);
    }

    protected function getProcessHandler()
    {
        $stepValidateDoc = new Step(
            'step_validate_doc',
            'Validate doc',
            array(),
            array('setStatus', 'Lexik\Bundle\WorkflowBundle\Tests\Fixtures\FakeModel::STATUS_VALIDATE')
        );

        $stepRemoveDoc = new Step(
            'step_remove_doc',
            'Remove doc',
            array(),
            array('setStatus', 'Lexik\Bundle\WorkflowBundle\Tests\Fixtures\FakeModel::STATUS_REMOVE')
        );

        $stepRemoveOnInvalidDoc = new Step(
            'step_remove_on_invalid_doc',
            'Remove doc',
            array(),
            array('setStatus', 'Lexik\Bundle\WorkflowBundle\Tests\Fixtures\FakeModel::STATUS_REMOVE'),
            array(),
            'step_fake'
        );

        $stepFake = new Step('step_fake', 'Fake', array());

        $stepCreateDoc = new Step(
            'step_create_doc',
            'Create doc',
            array(),
            array('setStatus', 'Lexik\Bundle\WorkflowBundle\Tests\Fixtures\FakeModel::STATUS_CREATE')
        );
        $stepCreateDoc->addNextState('validate', NextStateInterface::TARGET_TYPE_STEP, $stepValidateDoc);
        $stepCreateDoc->addNextState('validate_with_pre_validation', NextStateInterface::TARGET_TYPE_STEP, $stepValidateDoc);
        $stepCreateDoc->addNextState('validate_with_pre_validation_invalid', NextStateInterface::TARGET_TYPE_STEP, $stepValidateDoc);
        $stepCreateDoc->addNextState('remove', NextStateInterface::TARGET_TYPE_STEP, $stepRemoveDoc);
        $stepCreateDoc->addNextState('remove_on_invalid', NextStateInterface::TARGET_TYPE_STEP, $stepRemoveOnInvalidDoc);

        $process = new Process(
            'document_proccess',
            array(
                'step_create_doc'            => $stepCreateDoc,
                'step_validate_doc'          => $stepValidateDoc,
                'step_remove_doc'            => $stepRemoveDoc,
                'step_remove_on_invalid_doc' => $stepRemoveOnInvalidDoc,
                'step_fake'                  => $stepFake,
            ),
            'step_create_doc',
            array('step_validate_doc')
        );

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener('document_proccess.step_fake.reached', array(
            new FakeProcessListener(), 'handleSucccess'
        ));
        $dispatcher->addListener('document_proccess.step_remove_doc.access_validation', array(
            new FakeValidatorListener(), 'invalid'
        ));
        $dispatcher->addListener('document_proccess.step_remove_on_invalid_doc.access_validation', array(
            new FakeValidatorListener(), 'invalid'
        ));
        $dispatcher->addListener('document_proccess.step_validate_doc.access_validation', array(
            new FakeValidatorListener(), 'valid'
        ));
        $dispatcher->addListener('document_proccess.step_create_doc.validate_with_pre_validation.pre_validation', array(
            new FakeValidatorListener(), 'valid'
        ));
        $dispatcher->addListener('document_proccess.step_create_doc.validate_with_pre_validation_invalid.pre_validation', array(
            new FakeValidatorListener(), 'invalid'
        ));
        $dispatcher->addListener('document_proccess.step_create_doc.validate_with_pre_validation_invalid.pre_validation_fail', array(
            new FakeProcessListener(), 'handleSucccess'
        ));

        $processHandler = new ProcessHandler($process, $this->modelStorage, $dispatcher);
        $processHandler->setSecurityContext(new FakeSecurityContext());

        return $processHandler;
    }
}
