<?php

namespace SamYapp\LaravelRemoteAuth;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Default callable to persist the user model's attributes from remote authentication
 */
class DefaultUserSyncer
{
    /**
     * @param AuthConfig $config
     * @param array
     * @return bool
     */
    public function __invoke(Authenticatable $user, array $attributes, AuthConfig $config)
    {
        if (method_exists($user, 'save')) {
            $user->save();
        }
    }
}