<?php

namespace Lexik\Bundle\WorkflowBundle\Validation;

class Violation
{
    /**
     * @var string
     */
    private $message;

    /**
     * Constructor.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
