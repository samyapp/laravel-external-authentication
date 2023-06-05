<?php

namespace Tests;

use App\Models\User;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use SamYapp\LaravelRemoteAuth\AuthAttribute;
use SamYapp\LaravelRemoteAuth\AuthConfig;
use SamYapp\LaravelRemoteAuth\DefaultAttributeMapper;
use SamYapp\LaravelRemoteAuth\DefaultUserCreator;
use SamYapp\LaravelRemoteAuth\DefaultUserSyncer;
use SamYapp\LaravelRemoteAuth\TransientUser;

/**
 * @covers \SamYapp\LaravelRemoteAuth\AuthConfig
 */
class AuthConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function fromArraySetsObjectProperties()
    {
        // simple attributes with non-default values to check they
        // get set
        $properties = [
            'id' => 'remote-authy',
            'attributePrefix' => 'SAML_',
            'credentialAttributes' => ['username', 'password'],
            'developmentMode' => true,
            'developmentAttributes' => ['foo' => 'bar', 'foobar' => true],
            'userProvider' => 'donuts',
            'mapAttributes' => fn () => 'test',
        ];
        $allProperties = array_merge($properties, [
            // these get converted to AuthAttribute[]
            'attributeMap' => ['username' => 'mail', 'password' => 'pass'],
        ]);
        $config = AuthConfig::fromArray($allProperties);
        // check public properties set
        foreach ($properties as $name => $value) {
            $this->assertEquals($value, $config->$name);
        }
        // check attribute mapper is set
        $this->assertEquals($allProperties['mapAttributes'], $config->attributeMapper());
        // check attributeMap has expected number
        $this->assertCount(count($allProperties['attributeMap']), $config->attributeMap);
        foreach ($config->attributeMap as $attribute) {
            $this->assertArrayHasKey($attribute->name, $allProperties['attributeMap']);
        }
        // ensure we are testing every property
        foreach ($allProperties as $key => $value) {
            $this->assertTrue(property_exists($config, $key));
        }
    }

    /**
     * @test
     */
    public function fromArrayThrowsInvalidArgumentExceptionForUnknownConfigurationSettings()
    {
        $this->expectException(\InvalidArgumentException::class);
        AuthConfig::fromArray(['foo' => 'bar']);
    }

    /**
     * @test
     */
    public function attributeMapperReturnsDefaultAttributeMapperIfmapAttributesNotSet()
    {
        $config = AuthConfig::fromArray([]);
        $this->assertInstanceOf(DefaultAttributeMapper::class, $config->attributeMapper());
    }

    /**
     * @test
     */
    public function attributeMapperReturnsConfiguredCallbackIfConfigured()
    {
        $config = AuthConfig::fromArray([
            'mapAttributes' => fn (AuthConfig $config, array $input) => 'hello',
        ]);
        // should not be the default
        $this->assertNotInstanceOf(DefaultAttributeMapper::class, $config->attributeMapper());
        $this->assertIsCallable($config->attributeMapper());
        // assert it is the callable we expect
        $this->assertEquals('hello', $config->attributeMapper()($config, []));
    }

    /**
	 * @test
	 */
    public function fromArrayDoesNotChangePropertiesWhenNotInInput()
    {
		$defaultConfig = AuthConfig::fromArray([]);
		$properties = [
			'developmentMode' => true,
		];
		$modifiedConfig = AuthConfig::fromArray($properties);
		foreach (['id', 'attributePrefix'] as $unchangedProperty) {
			$this->assertEquals($defaultConfig->$unchangedProperty, $modifiedConfig->$unchangedProperty);
		}
    }

    /**
     * @test
     */
    public function attributesFromArrayCreatesAttributesIndexedByName()
    {
        $input = [
            // optional attribute
            new AuthAttribute('attr', 'remoteAttr', false),
            // required attribute where both attribute and remote name are "sameName"
            'sameName',
            // required attribute named "localName" from remote var "remoteName"
            'localName' => 'remoteName',
            // required attribute "requiredrequiredAttribute" with remote name "remoteAttribute"
            'requiredAttribute' => ['remote' => 'remoteAttribute', 'required' => true],
            // optional attribute "optionalAttribute" with remote name "remoteAttribute"
            'optionalAttribute' => ['remote' => 'remoteAttribute', 'required' => false],
        ];
        $attributes = AuthConfig::attributesFromArray($input);

        $this->assertEquals('sameName', $attributes['sameName']->name);
        $this->assertEquals('sameName', $attributes['sameName']->remoteName);
        $this->assertTrue($attributes['sameName']->required);

        $this->assertEquals('localName', $attributes['localName']->name);
        $this->assertEquals('remoteName', $attributes['localName']->remoteName);
        $this->assertTrue($attributes['localName']->required);

        $this->assertEquals('requiredAttribute', $attributes['requiredAttribute']->name);
        $this->assertEquals('remoteAttribute', $attributes['requiredAttribute']->remoteName);
        $this->assertTrue($attributes['requiredAttribute']->required);

        $this->assertEquals('optionalAttribute', $attributes['optionalAttribute']->name);
        $this->assertEquals('remoteAttribute', $attributes['optionalAttribute']->remoteName);
        $this->assertFalse($attributes['optionalAttribute']->required);

        // should have just added the AuthAttribute from input
        $this->assertEquals($input[0], $attributes['attr']);
    }
}