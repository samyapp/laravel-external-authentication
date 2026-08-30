<?php

namespace Tests;

use SamYapp\LaravelExternalAuth\Events\UnknownUserAuthenticating;
use SamYapp\LaravelExternalAuth\ExternalAuthGuard;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\SamYapp\LaravelExternalAuth\Events\UnknownUserAuthenticating::class)]
class UnknownUserAuthenticatingTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function constructorSetsAttributesArrayAndGuardObjectToPublicProperties()
    {
        $guard = $this->createStub(ExternalAuthGuard::class);
        $attrs = ['foo' => 'bar'];
        $obj = new UnknownUserAuthenticating($attrs, $guard);
        $this->assertEquals($guard, $obj->guard);
        $this->assertEquals($attrs, $obj->attributes);
    }
}