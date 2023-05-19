<?php

namespace SamYapp\LaravelRemoteAuth;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class TransientUserProvider implements UserProvider
{
    
    public function retrieveById($identifier)
    {
        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    public function retrieveByCredentials(array $credentials)
    {
        return new GenericUser($credentials);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // always returns true
        return true;
    }

}