<?php

namespace FreeAgent\WorkflowBundle\Entity;

/**
 * Used to store a state of a model object.
 *
 */
class ModelState
{
    protected $id;

    protected $workflowIdentifier;

    protected $processName;

    protected $stepName;

    protected $reachedAt;

    protected $data;

    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get workflowIdentifier
     *
     * @return string
     */
    public function getWorkflowIdentifier()
    {
        return $this->workflowIdentifier;
    }

    /**
     * Set workflowIdentifier
     *
     * @param string $workflowIdentifier
     */
    public function setWorkflowIdentifier($workflowIdentifier)
    {
        $this->workflowIdentifier = $workflowIdentifier;
    }

    /**
     * Get processName
     *
     * @return string
     */
    public function getProcessName()
    {
        return $this->processName;
    }

    /**
     * Set processName
     *
     * @param string $processName
     */
    public function setProcessName($processName)
    {
        $this->processName = $processName;
    }

    /**
     * Get stepName
     *
     * @return string
     */
    public function getStepName()
    {
        return $this->stepName;
    }

    /**
     * Set stepName
     *
     * @param string $stepName
     */
    public function setStepName($stepName)
    {
        $this->stepName = $stepName;
    }

    /**
     * Get reachedAt
     *
     * @return \DateTime
     */
    public function getReachedAt()
    {
        return $this->reachedAt;
    }

    /**
     * Set reachedAt
     *
     * @param DateTime $reachedAt
     */
    public function setReachedAt($reachedAt)
    {
        $this->reachedAt = $reachedAt;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * PrePersist callback.
     *
     */
    public function prePersist()
    {
        $this->reachedAt = new \DateTime('now');
    }
}