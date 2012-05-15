<?php

namespace FreeAgent\Bundle\WorkflowBundle\Validator;

class Example implements ValidatorInterface
{
    public function validate($model)
    {
        return true;
    }
}
