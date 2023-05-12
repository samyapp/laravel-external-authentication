<?php

namespace Tests;

use SamYapp\LaravelRemoteAuth\AuthAttribute;
use SamYapp\LaravelRemoteAuth\AuthConfig;
use SamYapp\LaravelRemoteAuth\DefaultAttributeMapper;

/**
 * @covers \SamYapp\LaravelRemoteAuth\AuthAttribute
 */
class AuthAttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function constructorSetsPropertiesThatCanBeAccessedLater()
    {
        $name = 'attribute name';
        $remoteName = 'remote attribute name';
        $required = false;
        $attribute = new AuthAttribute($name, $remoteName, $required);
        $this->assertEquals($name, $attribute->name);
        $this->assertEquals($remoteName, $attribute->remoteName);
        $this->assertEquals($required, $attribute->required);
    }
}