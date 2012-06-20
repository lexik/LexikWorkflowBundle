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

    protected $successful;

    protected $createdAt;

    protected $data;

    protected $errors;

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
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return json_decode($this->data, true);
    }

    /**
     * Set data
     *
     * @param string $data
     */
    public function setData($data)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }

        $this->data = $data;
    }

    /**
     * Get successful
     *
     * @return boolean
     */
    public function getSuccessful()
    {
        return $this->successful;
    }

    /**
     * Set successful
     *
     * @param boolean
     */
    public function setSuccessful($successful)
    {
        $this->successful = (boolean) $successful;
    }

    /**
     * Get errors
     *
     * @return string
     */
    public function getErrors()
    {
        return json_decode($this->errors, true);
    }

    /**
     * Set errors
     *
     * @param string $errors
     */
    public function setErrors($errors)
    {
        if (!is_string($errors)) {
            $errors = json_encode($errors);
        }

        $this->errors = $errors;
    }

    /**
     * PrePersist callback.
     *
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now');
    }
}