<?php

namespace SamYapp\LaravelRemoteAuth;

use App\Models\User;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
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

    /** @var bool - true if logout() has been called in the current request, unless login() has since been called */
    protected $loggedOut = false;

    /**
     * Create a new authentication guard.

     * @param array $config - array of configuration information
     */
    public function __construct(
        AuthConfig  $config,
        UserProvider $provider,
        array     $input,
        Logger  $logger,
    )
    {
        $this->setProvider($provider);
        $this->config = $config;
        $this->input = $input;
        $this->logger = $logger;
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
                    $this->user = $this->getProvider()->retrieveByCredentials($credentials);
                    // if the user wasn't found
                    if (!$this->user) {
                        // can we create a new one?
                        if ($this->config->createMissingUsers) {
                            $this->user = $this->config->userCreator()($userAttributes);
                            if (!$this->user) {
                                $this->logger->warning(
                                    sprintf(
                                        '%s::%s - unable to create new user with attributes',
                                        __CLASS__,
                                        __METHOD__
                                    ),
                                    $userAttributes
                                );
                            }
                        } else {
                            // log that authentication failed
                            $this->logger->notice(sprintf('%s::%s - authentication failed for credentials', __CLASS__,__METHOD__), $credentials);
                        }
                    }
                    if ($this->user) {
                        // assign the userAttributes to the user object
                        $this->setAttributes($this->user, $userAttributes);
                        // should we persist user attributes from remote with internal model?
                        $this->config->syncUser && $this->config->userSyncer()($this->user, $userAttributes, $config);
                    }
                } else {
                    // attributes present but invalid
                    $this->logger->warning(sprintf('%s::%s - attributes missing', __CLASS__, __METHOD__),$missingAttributes);
                }
            } else {
                // no attributes set at all
                $this->logger->notice(
                    sprintf('%s::%s - no attributes present', __CLASS__, __METHOD__),
                    $this->input
                );
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
        $this->setUser($user);
        $this->loggedOut = false;
    }

    /**
     * @inheritDoc
     */
    public function logout($redirect_to = '/')
    {
        $this->user = null;
        $this->loggedOut = true;
    }
}
