<?php

namespace Tests;

use SamYapp\LaravelExternalAuth\Events\UnknownUserAuthenticating;
use SamYapp\LaravelExternalAuth\ExternalAuthGuard;

/**
 * @covers \SamYapp\LaravelExternalAuth\Events\UnknownUserAuthenticating
 */
class UnknownUserAuthenticatingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function constructorSetsAttributesArrayAndGuardObjectToPublicProperties()
    {
        $guard = $this->createMock(ExternalAuthGuard::class);
        $attrs = ['foo' => 'bar'];
        $obj = new UnknownUserAuthenticating($attrs, $guard);
        $this->assertEquals($guard, $obj->guard);
        $this->assertEquals($attrs, $obj->attributes);
    }
}