<?php

namespace Lexik\Bundle\WorkflowBundle\Tests\Fixtures;

use Lexik\Bundle\WorkflowBundle\Entity\ModelState;
use Lexik\Bundle\WorkflowBundle\Model\ModelInterface;

class FakeModel implements ModelInterface
{
    const STATUS_CREATE   = 1;
    const STATUS_VALIDATE = 2;
    const STATUS_REMOVE   = 3;

    protected $status;

    protected $content;

    public $data = array();

    public $states = array();

    public function getWorkflowIdentifier()
    {
        return 'sample_identifier';
    }

    public function getWorkflowData()
    {
        return $this->data;
    }

    public function addState(ModelState $modelState)
    {
        $this->states[] = $modelState;
    }

    public function getStates()
    {
        return $this->states;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }
}
