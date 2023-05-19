<?php

namespace Tests;

use Illuminate\Auth\DatabaseUserProvider;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use SamYapp\LaravelRemoteAuth\AuthConfig;
use SamYapp\LaravelRemoteAuth\RemoteAuthGuard;

/**
 * @covers \SamYapp\LaravelRemoteAuth\RemoteAuthGuard
 */
class RemoteAuthGuardTest extends \Orchestra\Testbench\TestCase
{
    /**
     * @test
     */
    public function constructorSetsConfigUserProviderAndRequest()
    {
        $auth = app('auth');
        $config = AuthConfig::fromArray(['id' => 'test-remote-auth']);
        $provider = $auth->getProvider('database');
        $input = ['foo' => 'bar'];
        $guard = new RemoteAuthGuard($config, $provider, $input, app(Logger::class));
        $this->assertEquals($config, $guard->config);
        $this->assertEquals($provider, $guard->getProvider());
        $this->assertEquals($input, $guard->input);
    }

    /**
     * @test
     */
    public function userReturnsTheAuthenticatedUserForMultipleCallsOnTheSameRequest()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * @test
     */
    public function userReturnsNullIfNoAuthenticationPresent()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function userSyncsAttributesWhenConfigSyncAttributesIsTrue()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function userDoesNotSyncAttributesWhenConfigSyncAttributesIsFalse()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function userCreatesAUserWhenConfigCreateMissingUsersIsTrue()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function userDoesNotCreateAUserAndAuthFailsWhenAuthAttributesPresentForInvalidUserAndConfigCreateMissingUsersIsFalse()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function userReturnsNullForIfNoAuthenticationPresent()
    {
        $this->markTestIncomplete();
    }
}