<?php

namespace SamYapp\LaravelRemoteAuth;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Default callable to create a new user model
 */
class DefaultUserCreator
{
    /**
     * Create a new User with the given attributes
     * @param array - properties to assign to the user object
     * @param AuthConfig $config 
     * @return bool
     */
    public function __invoke(array $attributes, AuthConfig $config): Authenticatable
    {
        $user = new $config->userModel;
        foreach ($attributes as $key => $value) {
            $user->$key = $value;
        }
        $user->save();
        return $user;
    }
}