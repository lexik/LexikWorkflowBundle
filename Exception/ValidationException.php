<?php

namespace FreeAgent\WorkflowBundle\Exception;

class ValidationException extends \Exception
{
     /**
      * (non-PHPdoc)
      * @see Exception::__toString()
      */
     public function __toString() {
         return $this->getMessage();
     }
}
