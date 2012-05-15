<?php

namespace FreeAgent\Bundle\WorkflowBundle\Model;

interface ModelInterface
{
    public function getWorkflowName();
    public function setWorkflowName($workflowName);
    public function setWorkflowStepName($stepName);
    public function getWorkflowStepName();
}
