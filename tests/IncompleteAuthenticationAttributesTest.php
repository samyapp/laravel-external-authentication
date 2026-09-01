<?php

namespace Tests;

use SamYapp\LaravelExternalAuth\AuthAttribute;
use SamYapp\LaravelExternalAuth\Events\IncompleteAuthenticationAttributes;
use SamYapp\LaravelExternalAuth\Events\UnknownUserAuthenticating;
use SamYapp\LaravelExternalAuth\ExternalAuthGuard;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\SamYapp\LaravelExternalAuth\Events\IncompleteAuthenticationAttributes::class)]
class IncompleteAuthenticationAttributesTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function constructorSetsParameterValuesToPublicProperties()
    {
        $guard = $this->createStub(ExternalAuthGuard::class);
        $attrs = ['foo' => 'bar'];
        $missing = [
            new AuthAttribute('missing1', 'x-missing-1', false),
            new AuthAttribute('missing2', 'x-missing-2', true),
        ];
        $obj = new IncompleteAuthenticationAttributes($missing, $attrs, $guard);
        $this->assertEquals($guard, $obj->guard);
        $this->assertEquals($attrs, $obj->attributes);
        $this->assertEquals($missing, $obj->missingAttributes);
    }
}