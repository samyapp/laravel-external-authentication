<?php

namespace Tests\Support;

use Illuminate\Foundation\Auth\User;

/**
 * Class to use when testing the DefaultUserCreator
 */
class TestUser extends User
{
    public $hasBeenSaved = false;
    public $table = 'users';

    public $roles = [];

    public function save(array $options = [])
    {
        $this->hasBeenSaved = true;
        $this->password = 'pa55w0rd';
        parent::save();
        return true;
    }
}