<?php

namespace Lexik\Bundle\WorkflowBundle\Tests\Fixtures;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FakeAuthorizationChecker implements AuthorizationCheckerInterface
{
    private $authenticatedUser;

    public $testedAttributes = null;

    public $testedObject = null;

    public function __construct($authenticatedUser)
    {
        $this->authenticatedUser = $authenticatedUser;
    }

    public function isGranted($attributes, $object = null)
    {
        $this->testedAttributes = $attributes;
        $this->testedObject = $object;

        return $this->authenticatedUser;
    }
}
