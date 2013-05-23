<?php

namespace Lexik\Bundle\WorkflowBundle\Tests\Fixtures;

use Lexik\Bundle\WorkflowBundle\Exception\ValidationException;

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
