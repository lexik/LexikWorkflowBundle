<?php

namespace Lexik\Bundle\WorkflowBundle\Tests\Fixtures;

use Lexik\Bundle\WorkflowBundle\Event\ValidateStepEvent;

class FakeValidatorListener
{
    public function valid(ValidateStepEvent $event)
    {
    }

    public function invalid(ValidateStepEvent $event)
    {
        $event->addViolation('Validation error!');
    }
}
