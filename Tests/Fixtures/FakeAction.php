<?php

namespace FreeAgent\WorkflowBundle\Tests\Fixtures;

class FakeAction
{
    static public $call = 0;

    public function call()
    {
        self::$call++;
    }
}
