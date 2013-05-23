<?php

namespace Lexik\Bundle\WorkflowBundle\Tests\Fixtures;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FakeSecurityContext implements SecurityContextInterface
{
    public function getToken()
    {
    }

    public function setToken(TokenInterface $token = null)
    {
    }

    public function isGranted($attributes, $object = null)
    {
        return true;
    }
}
