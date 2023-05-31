<?php

namespace SamYapp\LaravelRemoteAuth;

use App\Http\Middleware\Authenticate;
use Illuminate\Http\Request;

/**
 * Simple data object for configuration options
 */
class AuthConfig
{
    /** @var AuthAttribute[] - the expected attribute definitions keyed by attribute name */
    public array $attributeMap = [];

    /** @var array|string[] names of attributes to pass to UserProvider::retrieveByCredentials */
    public array $credentialAttributes = ['email'];

    /** @var string - the name for this auth guard */
	public string $id = 'remote-auth';

	/** @var bool - should users be created if they are authenticated but don't exist */
	public bool $createMissingUsers = false;

    /** @var mixed|null -*/
    protected mixed $userCreator = null;

	/** @var string - optional prefix to prepend when retrieving attributes from headers or environment variables */
	public string $attributePrefix = '';

    /**
     * @var array [remoteName => value] attributes to make available as server vars
     * for use in development environment without a real remote authentication proxy configured
     */
    public array $developmentAttributes = [];

    /** @var bool - whether or not to add $developmentAttributes to server vars */
    public bool $developmentMode = false;

    /** @var string - the key in config/auth.php 'providers' defining which user provider to use */
    public string $userProvider = 'users';

    /** @var null|callable - optional callable to map remote variables to user attributes */
    protected mixed $mapAttributes = null;

    /** @var callabe|null - optional callable to persist changed user attributes  */
    protected mixed $syncUser = null;
    
	/**
	 * Initialise an AuthConfig from a config array
	 * @param array $config - configuration options as returned by config('remote-auth')
	 * @return AuthConfig
	 */
	public static function fromArray(array $config): AuthConfig
	{
		$instance = new static();
		if (isset($config['attributeMap'])) {
			$config['attributeMap'] = static::attributesFromArray($config['attributeMap']);
		}
        foreach ($config as $key => $value) {
            if (property_exists($instance, $key)) {
				$instance->$key = $value;
			} else {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid AuthConfig setting', $key));
            }
		}
		return $instance;
	}

    /**
     * Get the callable to sync the user attributes with the database / storage
     * @return callable - (Authenticatble $user, AuthConfig $config): void
     */
    public function userSyncer(): callable
    {
        return is_callable($this->syncUser) ? $this->syncUser : ($this->syncUser = new DefaultUserSyncer());
    }
    
    /**
     * @return callable - (AuthConfig $config, array $remoteData) => [ user-attributes]
     */
    public function attributeMapper(): callable
    {
        return is_callable($this->mapAttributes) ? $this->mapAttributes : ($this->mapAttributes = new DefaultAttributeMapper());
    }


    /**
	 * Create an array of AuthAttribute from an array in config format
	 * @param array $attrs
	 * @return AuthAttribute[]
	 */
	public static function attributesFromArray(array $attrs): array
	{
		$attributes = [];
		foreach ($attrs as $attributeName => $attributeDetails) {
			// each entry in attributeMap can be in the form:
			// 'attributeName',
			// 'attributeName' => 'remoteName',
			// or 'attributeName' => [ 'remote' => 'remoteName', 'required' => true|false ]
			// where either of 'remote' or 'required' may be absent

            // ['attributeName' => ['required' => false, 'remote' => 'remoteName'],]
			if (is_array($attributeDetails)) {
                $attributes[$attributeName] = new AuthAttribute(
                    $attributeName,
         $attributeDetails['remote'] ?? $attributeName,
            $attributeDetails['required'] ?? true
                );
            // ['attributeName' => 'remoteName',]
			} else if (is_string($attributeName) && !empty($attributeName)) {
                $attributes[$attributeName] = new AuthAttribute(
                    $attributeName,
                    $attributeDetails ?? $attributeName,
                    true
                );
            // ['attributeName',]
			} else if (is_string($attributeDetails) && !empty($attributeDetails)) {
                $attributes[$attributeDetails] = new AuthAttribute(
                    $attributeDetails,
                    $attributeDetails,
                    true,
                );
            } else if ($attributeDetails instanceof AuthAttribute) {
                $attributes[$attributeDetails->name] = $attributeDetails;
			} else {
				throw new \InvalidArgumentException(
					sprintf(
					'Attribute specification %s is invalid: %s',
					$attributeName,
					var_export($attributeDetails ,true)
					)
				);
			}
		}
		return $attributes;
	}
}