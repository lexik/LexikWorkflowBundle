<?php

namespace Lexik\Bundle\WorkflowBundle\Tests\Fixtures;

class FakeProcessListener
{
    public static $call = 0;

    public function handleSucccess()
    {
        self::$call++;
    }
}
