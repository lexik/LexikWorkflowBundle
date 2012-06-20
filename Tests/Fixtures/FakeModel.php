<?php

namespace FreeAgent\WorkflowBundle\Tests\Fixtures;

use FreeAgent\WorkflowBundle\Model\ModelInterface;

class FakeModel implements ModelInterface
{
    public $data = array();

    public function getWorkflowIdentifier()
    {
        return 'sample_identifier';
    }

    public function getWorkflowData()
    {
        return $this->data;
    }
}
