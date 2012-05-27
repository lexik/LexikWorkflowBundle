<?php

namespace FreeAgent\WorkflowBundle\Model;

interface ModelInterface
{
    public function getWorkflowName();
    public function setWorkflowName($workflowName);
    public function setWorkflowStepName($stepName);
    public function getWorkflowStepName();
    public function setWorkflowStepComment($stepComment);
    public function getWorkflowStepComment();
    public function setWorkflowStepAt($stepAt = time());
    public function getWorkflowStepAt();
}
