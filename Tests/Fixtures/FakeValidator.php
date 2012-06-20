<?php

namespace FreeAgent\WorkflowBundle\Tests\Fixtures;

use FreeAgent\WorkflowBundle\Exception\ValidationException;

class FakeValidator
{
    public function valid()
    {
    }

    public function invalid()
    {
        throw new ValidationException('Validator error!');
    }
}
