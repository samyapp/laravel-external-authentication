<?php

namespace Tests;

use SamYapp\LaravelRemoteAuth\Events\UnknownUserAuthenticating;
use SamYapp\LaravelRemoteAuth\RemoteAuthGuard;

/**
 * @covers \SamYapp\LaravelRemoteAuth\Events\UnknownUserAuthenticating
 */
class UnknownUserAuthenticatingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function constructorSetsAttributesArrayAndGuardObjectToPublicProperties()
    {
        $guard = $this->createMock(RemoteAuthGuard::class);
        $attrs = ['foo' => 'bar'];
        $obj = new UnknownUserAuthenticating($attrs, $guard);
        $this->assertEquals($guard, $obj->guard);
        $this->assertEquals($attrs, $obj->attributes);
    }
}