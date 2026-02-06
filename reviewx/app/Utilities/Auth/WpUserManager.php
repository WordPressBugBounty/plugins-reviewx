<?php

namespace Rvx\Utilities\Auth;

class WpUserManager
{
    protected bool $isUserLoggedIn = \false;
    protected bool $userCan = \false;
    public function __construct(bool $isLoggedIn = \false, bool $userCan = \false)
    {
        $this->isUserLoggedIn = $isLoggedIn;
        $this->userCan = $userCan;
    }
    public function setLoggedInStatus(bool $isLoggedIn)
    {
        $this->isUserLoggedIn = $isLoggedIn;
    }
    public function setAbility(bool $userCan)
    {
        $this->userCan = $userCan;
    }
    public function isLoggedIn() : bool
    {
        return $this->isUserLoggedIn;
    }
    public function can() : bool
    {
        return $this->userCan;
    }
}
