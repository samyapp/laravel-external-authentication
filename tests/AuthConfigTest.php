<?php

namespace Tests;

use App\Models\User;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use SamYapp\LaravelExternalAuth\AuthAttribute;
use SamYapp\LaravelExternalAuth\AuthConfig;
use SamYapp\LaravelExternalAuth\DefaultAttributeMapper;
use SamYapp\LaravelExternalAuth\DefaultUserCreator;
use SamYapp\LaravelExternalAuth\DefaultUserSyncer;
use SamYapp\LaravelExternalAuth\TransientUser;

/**
 * @covers \SamYapp\LaravelExternalAuth\AuthConfig
 * @covers \SamYapp\LaravelExternalAuth\AuthAttribute
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
            'id' => 'external-authy',
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
    public function defaultConfigSetsDevelopmentModeFalse()
    {
        $config = AuthConfig::fromArray([]);
        $this->assertFalse($config->developmentMode);
    }

    /**
     * @test
     */
    public function defaultConfigSetsUserProviderNameToUsers()
    {
        $config = AuthConfig::fromArray([]);
        $this->assertEquals('users', $config->userProvider);
    }

    /**
     * @test
     */
    public function defaultConfigSetsAttributePrefixToEmptyString()
    {
        $config = AuthConfig::fromArray([]);
        $this->assertEquals( '', $config->attributePrefix);
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
            new AuthAttribute('attr', 'externalAttr', false),
            // required attribute where both attribute and external name are "sameName"
            'sameName',
            // required attribute named "localName" from external var "externalName"
            'localName' => 'externalName',
            // required attribute "requiredrequiredAttribute" with external name "externalAttribute"
            'requiredAttribute' => ['external' => 'externalAttribute', 'required' => true],
            // optional attribute "optionalAttribute" with external name "externalAttribute"
            'optionalAttribute' => ['external' => 'externalAttribute', 'required' => false],
        ];
        $attributes = AuthConfig::attributesFromArray($input);

        $this->assertEquals('sameName', $attributes['sameName']->name);
        $this->assertEquals('sameName', $attributes['sameName']->externalName);
        $this->assertTrue($attributes['sameName']->required);

        $this->assertEquals('localName', $attributes['localName']->name);
        $this->assertEquals('externalName', $attributes['localName']->externalName);
        $this->assertTrue($attributes['localName']->required);

        $this->assertEquals('requiredAttribute', $attributes['requiredAttribute']->name);
        $this->assertEquals('externalAttribute', $attributes['requiredAttribute']->externalName);
        $this->assertTrue($attributes['requiredAttribute']->required);

        $this->assertEquals('optionalAttribute', $attributes['optionalAttribute']->name);
        $this->assertEquals('externalAttribute', $attributes['optionalAttribute']->externalName);
        $this->assertFalse($attributes['optionalAttribute']->required);

        // should have just added the AuthAttribute from input
        $this->assertEquals($input[0], $attributes['attr']);
    }

    /**
     * @test
     */
    public function attributesFromArrayThrowsInvalidArgumentExceptionForInvalidInputFormat()
    {
        $this->expectException(\InvalidArgumentException::class);
        AuthConfig::attributesFromArray([
            0
        ]);
    }
}