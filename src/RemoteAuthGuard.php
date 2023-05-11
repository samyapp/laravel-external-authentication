<?php

namespace SamYapp\LaravelRemoteAuth;

use App\Models\User;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Authenticates users based on environment variables or http headers
 * @package SamYapp\LaravelRemoteAuth
 */
class RemoteAuthGuard implements Guard
{
    // Laravel trait that implements some required methods
    use GuardHelpers;

    /** @var AuthConfig - configuration information */
    public AuthConfig $config;

    /**
     * Create a new authentication guard.

     * @param array $config - array of configuration information
     */
    public function __construct(
        UserProvider $provider,
        AuthConfig  $config,
        Request     $request,
    )
    {
        $this->setProvider($provider);
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function user()
    {
        // if we already have a user for *this request* we don't need to redo everything
        if (is_null($this->user)) {
            if ($userAttributes = $this->getRemoteAttributes($this->config, $this->request->server())) {
                if ($userAttributes = $this->validateAttributes($this->config, $userAttributes)) {
                    $credentials = array_intersect_key($userAttributes, array_flip($this->config->credentialAttributes));
                    $this->user = $this->getProvider()->retrieveByCredentials($credentials);
                    if (!$this->user && $this->config->createMissingUsers) {
                        $this->user = $this->createUserFromAttributes($remoteData);
                    }
                    if ($this->user && $this->config->syncAttributes) {
                        $this->syncUser($this->user, $this->config);
                    }
                } else {
                    // log attributes were invalid, optionally throw an exception
                }
            } else {
                // log no attributes passed
            }
        }
        return $this->user;
    }

    public function syncUser(AuthConfig $config, Authenticatable $user)
    {
        $user->save();
    }

    public function getRemoteAttributes(AuthConfig $config, array $input): array|false
    {
        $mappedAttributes = $config->attributeMapper()($config, $input);
        return $this->validateAttributes($config, $mappedAttributes);
    }

    public function validateAttributes(AuthConfig $config, array $attributes): array|false
    {
        return $attributes;
    }

    /**
     * Get the user object from the UserProvider based on credentials / saml attributes
     * @param UserProvider $provider
     * @param array $attributes
     * @return Authenticatable
     */
    public function getAuthenticatedUser(UserProvider $provider, array $attributes)
    {
        if (($user = $provider->retrieveByCredentials($attributes))) {
            return $user;
        }
        return null;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * (a user might make it passed remote auth but not have an attribute required for this app).
     *
     * @return bool
     */
    public function check()
    {
        return $this->user() !== null;
    }

    /**
     * Cannot validate the credentials as we have no access to these by design of SSO
     */
    public function validate(array $credentials = [])
    {
        throw new UnsupportedException('Validation of credentials is not supported');
    }

    /**
     * @inheritDoc
     */
    public function logout($redirect_to = '/')
    {
        $this->user = null;
    }
}
