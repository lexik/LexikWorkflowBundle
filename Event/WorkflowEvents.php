<?php

namespace FreeAgent\WorkflowBundle\Event;

/**
 * List all workflow events.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class WorkflowEvents
{
    /**
     * Thrown when a step is successfuly reached.
     */
    const STEP_REACHED = 'process.step_reached';

    /**
     * Thrown when you attempt to reach a step and some validation error occurred.
     */
    const STEP_VALIDATION_FAIL = 'process.step_validation_fail';
}
