<?php

namespace Tests;

use SamYapp\LaravelExternalAuth\AuthAttribute;
use SamYapp\LaravelExternalAuth\AuthConfig;
use SamYapp\LaravelExternalAuth\DefaultAttributeMapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\SamYapp\LaravelExternalAuth\AuthAttribute::class)]
class AuthAttributeTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
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