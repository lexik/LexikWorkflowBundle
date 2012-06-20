<?php

namespace FreeAgent\WorkflowBundle\Entity;

/**
 * Used to store a state of a model object.
 *
 */
class ModelState
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $workflowIdentifier;

    /**
     * @var string
     */
    protected $processName;

    /**
     * @var string
     */
    protected $stepName;

    /**
     * @var \DateTime
     */
    protected $reachedAt;

    /**
     * @var array
     */
    protected $data;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->reachedAt = new \DateTime('now');
    }

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
     * @return array
     */
    public function getData()
    {
        return json_decode($this->data, true);
    }

    /**
     * Set data
     *
     * @param mixed $data An array or a JSON string
     */
    public function setData($data)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }

        $this->data = $data;
    }
}