<?php

namespace Lexik\Bundle\WorkflowBundle\Tests\Fixtures;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FakeSecurityContext implements SecurityContextInterface
{
    private $authenticatedUser;

    public $testedAttributes = null;

    public $testedObject = null;

    public function __construct($authenticatedUser)
    {
        $this->authenticatedUser = $authenticatedUser;
    }

    public function getToken()
    {
    }

    public function setToken(TokenInterface $token = null)
    {
    }

    public function isGranted($attributes, $object = null)
    {
        $this->testedAttributes = $attributes;
        $this->testedObject = $object;
        return $this->authenticatedUser;
    }
}
