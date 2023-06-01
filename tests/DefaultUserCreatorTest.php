<?php

namespace Tests;

use Illuminate\Foundation\Auth\User;
use SamYapp\LaravelRemoteAuth\AuthConfig;
use SamYapp\LaravelRemoteAuth\DefaultUserCreator;
use Tests\Support\TestUser;

/**
 * @covers \SamYapp\LaravelRemoteAuth\DefaultUserCreator
 */
class DefaultUserCreatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function __invokeSetsAttributeValuesCallsSaveOnCreatedUserModelObject()
    {
        $attrs = ['name' => 'foo', 'email' => 'bar@example.com'];
        $config = AuthConfig::fromArray(['userModel' => TestUser::class]);
        $user = (new DefaultUserCreator())($attrs, $config);
        foreach ($attrs as $name => $value) {
            $this->assertEquals($value, $user->$name);
        }
        $this->assertTrue($user->hasBeenSaved);
        $this->assertInstanceOf(TestUser::class, $user);
    }
}