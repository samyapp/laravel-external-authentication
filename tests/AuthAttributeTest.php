<?php

namespace Tests;

use SamYapp\LaravelExternalAuth\AuthAttribute;
use SamYapp\LaravelExternalAuth\AuthConfig;
use SamYapp\LaravelExternalAuth\DefaultAttributeMapper;

/**
 * @covers \SamYapp\LaravelExternalAuth\AuthAttribute
 */
class AuthAttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function constructorSetsPropertiesThatCanBeAccessedLater()
    {
        $name = 'attribute name';
        $externalName = 'external attribute name';
        $required = false;
        $attribute = new AuthAttribute($name, $externalName, $required);
        $this->assertEquals($name, $attribute->name);
        $this->assertEquals($externalName, $attribute->externalName);
        $this->assertEquals($required, $attribute->required);
    }
}