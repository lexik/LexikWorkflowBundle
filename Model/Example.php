<?php

namespace FreeAgent\Bundle\WorkflowBundle\Model;

class Example implements ModelInterface
{
    private $workflow_step_name;
    private $workflow_name = 'example';

    public function getWorkflowName()
    {
        return $this->workflow_name;
    }

    public function setWorkflowName($workflowName)
    {
        return $this->workflow_name = $workflowName;
    }

    public function setWorkflowStepName($stepName)
    {
        $this->workflow_step_name = $stepName;
    }

    public function getWorkflowStepName()
    {
        return $this->workflow_step_name;
    }
}
