<?php

namespace SamYapp\LaravelRemoteAuth;

use App\Http\Middleware\Authenticate;
use Illuminate\Http\Request;

/**
 * Simple data object for configuration options
 */
class AuthConfig
{
	/** @var string - the name for this auth guard */
	public string $id = 'remote-auth';

	/** @var bool - should users be created if they are authenticated but don't exist? */
	public bool $createMissingUsers = false;

	/** @var string - optional prefix to prepend when retrieving attributes from headers or environment variables */
	public string $attributePrefix = '';

	/** @var ?string - if no header with this name is present, auth will fail */
	public ?string $requiredHeaderName = null;

	/** @var ?string - if not null, then if the required header does not have this value, auth will fail */
	public ?string $requiredHeaderValue = null;
    
    /** @var AuthAttribute[] - the expected attribute definitions */
	public array $expectedAttributes = [];

    /** @var array|string[] attributes to pass to UserProvider::retrieveByCredentials */
    public array $credentialAttributes = ['email'];

    /** @var array|string[] attribute names to update on the persisted user model */
    public array $syncAttributes = [];

    /**
     * @var array [remoteName => value] attributes to make available as server vars
     * for use in development environment without a real remote authentication proxy configured
     */
    public array $developmentAttributes = [];

    /** @var bool - whether or not to add $developmentAttributes to server vars */
    public bool $developmentMode = false;

    /** @var null|callable - optional callable to map remote variables to user attributes */
    protected mixed $mapAttributes = null;
    
	/**
	 * Initialise an AuthConfig from a config array
	 * @param array $config - configuration options as returned by config('remote-auth')
	 * @return AuthConfig
	 */
	public static function fromArray(array $config): AuthConfig
	{
		$instance = new static();
		if (isset($config['expectedAttributes'])) {
			$config['expectedAttributes'] = static::attributesFromArray($config['expectedAttributes']);
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
			// each entry in expectedAttributes can be in the form:
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