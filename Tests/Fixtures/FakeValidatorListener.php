<?php

namespace Lexik\Bundle\WorkflowBundle\Tests\Fixtures;

use Lexik\Bundle\WorkflowBundle\Event\StepAccessValidationEvent;

class FakeValidatorListener
{
    public function valid(StepAccessValidationEvent $event)
    {
    }

    public function invalid(StepAccessValidationEvent $event)
    {
        $event->addViolation('Validation error!');
    }
}
