<?php

namespace FreeAgent\WorkflowBundle\Tests\Fixtures;

class FakeProcessListener
{
    static public $call = 0;

    public function handleSucccess()
    {
        self::$call++;
    }
}
