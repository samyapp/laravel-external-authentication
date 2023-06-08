<?php

namespace SamYapp\LaravelExternalAuth;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Events\Dispatcher;
use SamYapp\LaravelExternalAuth\Events\IncompleteAuthenticationAttributes;
use SamYapp\LaravelExternalAuth\Events\UnknownUserAuthenticating;

/**
 * Authenticates users based on environment variables or http headers
 * @package SamYapp\LaravelExternalAuth
 */
class ExternalAuthGuard implements Guard
{
    // Laravel trait that implements some required methods
    use GuardHelpers;

    /** @var AuthConfig - configuration information */
    public AuthConfig $config;

    /** @var Dispatcher - event dispatcher */
    public Dispatcher $dispatcher;

    /** @var array - input data from external request */
    public array $input;

    /** @var string - the name of this guard in the auth configuration (default is 'web') */
    public string $guardName;

    /** @var bool - true if logout() has been called in the current request, unless login() has since been called */
    protected $loggedOut = false;

    /**
     * Create a new authentication guard.

     * @param AuthConfig $config - Configuration object
     * @param UserProvider $provider - User Provider to retrieve matching user
     * @param array $input - The input to process (e.g. Request::server() or similar)
     * @param Dispatcher $dispatcher - The event dispatcher to dispatch events with
     * @param string $name - The name of the guard in the config/auth.php
     */
    public function __construct(
        AuthConfig  $config,
        UserProvider $provider,
        array     $input,
        Dispatcher $dispatcher,
        string $name = 'web',
    )
    {
        $this->setProvider($provider);
        $this->config = $config;
        $this->input = $input;
        $this->dispatcher = $dispatcher;
        $this->guardName = $name;
    }

    /**
     * @inheritDoc
     */
    public function user()
    {
        // if we already have a user for *this request* we don't need to redo everything
        if (!$this->loggedOut && is_null($this->user)) {
            if ($userAttributes = $this->config->attributeMapper()($this->config, $this->input)) {
                // if we have attributes, do they meet our validation criteria?
                if (!($missingAttributes = $this->getMissingRequiredAttributes($this->config, $userAttributes))) {
                    // use the attributes we consider credentials to retrieve the user
                    $credentials = array_intersect_key($userAttributes, array_flip($this->config->credentialAttributes));
                    $user = $this->getProvider()->retrieveByCredentials($credentials);
                    if ($user) {
                        $this->setAttributes($user, $userAttributes);
                        $this->setUser($user);
                    } else {
                        $this->dispatcher?->dispatch(new UnknownUserAuthenticating($userAttributes, $this));
                    }
                } else {
                    // attributes present but missing some required ones
                    $this->dispatcher?->dispatch(new IncompleteAuthenticationAttributes($missingAttributes, $userAttributes, $this));
                }
            }
        }
        return $this->user;
    }

    /**
     * Sets the attributes for the user
     * @param Authenticatable $user
     * @param array $userAttributes
     * @return void
     */
    public function setAttributes(Authenticatable $user, array $userAttributes)
    {
        foreach ($userAttributes as $key => $value) {
            $user->$key = $value;
        }
    }

    /**
     * Gets an array containing the required AuthAttributes missing from the input array
     * @param AuthConfig $config
     * @param array $attributes - key => value of attributes passed to the app
     * @return AuthAttribute[]
     */
    public function getMissingRequiredAttributes(AuthConfig $config, array $attributes): array
    {
        return array_filter(
            array_diff_key($config->attributeMap, $attributes), fn (AuthAttribute $attr) => $attr->required
        );
    }

    /**
     * Cannot validate the credentials as we have no access to these by design of SSO
     */
    public function validate(array $credentials = [])
    {
        return false;
    }

    /**
     * Log the given user into the application. This isn't part of the Guard interface, but is referenced
     * in Laravel documentation.
     * @param Authenticatable $user
     * @return void
     */
    public function login(Authenticatable $user)
    {
        $this->dispatcher?->dispatch(new Login($this->guardName, $user, false));
        $this->setUser($user);
    }

    /**
     * Set the authenticated user to $user
     * @param Authenticatable $user
     * @return $this|void
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        $this->loggedOut = false;
        $this->dispatcher?->dispatch(new Authenticated($this->guardName, $user));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function logout()
    {
        $this->dispatcher?->dispatch(new Logout($this->guardName, $this->user));
        $this->user = null;
        $this->loggedOut = true;
    }
}
