<?php

namespace Tests\Support;

use Illuminate\Foundation\Auth\User;

/**
 * Class to use when testing the DefaultUserCreator
 */
class TestUser extends User
{
    public $hasBeenSaved = false;

    public function save(array $options = [])
    {
        $this->hasBeenSaved = true;
        return true;
    }
}