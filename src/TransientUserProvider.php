<?php

namespace SamYapp\LaravelExternalAuth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Polyfill\Intl\Icu\Exception\MethodNotImplementedException;

/**
 * UserProvider that "retrieves" a "user" by creating a new instance of the user model
 * and then assigning each key => value pair in the $credentials to that object
 */
class TransientUserProvider implements UserProvider
{
    public function __construct(
        /** @var the class to create user objects in */
        public string $modelClass,
        /** @var the name of the attribute that uniquely identifies the user (e.g. 'uid', 'username', 'email') */
        public ?string $authIdentifierName = null,
    )
    {
    }

    public function retrieveByCredentials(array $credentials)
    {
        $user = new $this->modelClass();
        if (method_exists($user, 'setAuthIdentifierName')) {
            $user->setAuthIdentifierName($this->authIdentifierName);
        }
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

    /** @codeCoverageIgnore */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        return;
    }
}