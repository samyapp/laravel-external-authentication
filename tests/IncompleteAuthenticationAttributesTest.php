<?php

namespace Tests;

use SamYapp\LaravelRemoteAuth\AuthAttribute;
use SamYapp\LaravelRemoteAuth\Events\IncompleteAuthenticationAttributes;
use SamYapp\LaravelRemoteAuth\Events\UnknownUserAuthenticating;
use SamYapp\LaravelRemoteAuth\RemoteAuthGuard;

/**
 * @covers \SamYapp\LaravelRemoteAuth\Events\IncompleteAuthenticationAttributes
 */
class IncompleteAuthenticationAttributesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function constructorSetsParameterValuesToPublicProperties()
    {
        $guard = $this->createMock(RemoteAuthGuard::class);
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