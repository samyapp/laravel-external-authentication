<?php

namespace SamYapp\LaravelExternalAuth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * UserProvider that "retrieves" a "user" by creating a new instance of the user model
 * and then assigning each key => value pair in the $credentials to that object
 */
class TransientUserProvider implements UserProvider
{
    public function __construct(/** @var the class to create user objects in */public string $modelClass) {}

    public function retrieveByCredentials(array $credentials)
    {
        $user = new $this->modelClass();
        foreach ($credentials as $name => $value) {
            $user->$name = $value;
        }
        return $user;
    }

    /** @codeCoverageIgnore */
    public function retrieveById($identifier)
    {
        return null;
    }

    /** @codeCoverageIgnore */
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    /** @codeCoverageIgnore */
    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    /** @codeCoverageIgnore */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return false;
    }

}