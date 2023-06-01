<?php

namespace Tests;

use App\Models\User;
use Illuminate\Auth\DatabaseUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use SamYapp\LaravelRemoteAuth\AuthConfig;
use SamYapp\LaravelRemoteAuth\RemoteAuthGuard;
use SamYapp\LaravelRemoteAuth\RemoteAuthServiceProvider;
use SamYapp\LaravelRemoteAuth\TransientUser;
use SamYapp\LaravelRemoteAuth\TransientUserProvider;

/**
 * @covers \SamYapp\LaravelRemoteAuth\RemoteAuthGuard
 */
class RemoteAuthGuardFeatureTest extends \Orchestra\Testbench\TestCase
{
    protected $developmentAttributes = ['foo' => 'bar', 'one' => 'two'];

    protected function getPackageProviders($app)
    {
        return [
            RemoteAuthServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('auth.guards.web.driver', 'remote-auth');
        // define a default config, but allow overriding with already configured by @define-env
        $app['config']->set('remote-auth', array_merge([
                'createMissingUsers' => false,
            ],
                // may have already been partially defined by @define-env
                $app['config']->get('remote-auth',[])
            )
        );
    }

    /**
     * @test
     */
    public function transientUserAuthenticatesWithCorrectAttributes()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function transientUserWithCustomUserModelAuthenticatesWithCorrectAttributes()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function existingPersistentUserAuthenticatesWithCorrectAttributesButDoesNotSyncWhenSyncUserIsFalse()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function existingPersistentUserAuthenticatesWithCorrectAttributesAndSyncsSyncUserIsTrue()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function existingPersistentUserAuthenticatesWithCorrectAttributesAndSyncsSyncUserIsCallable()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function missingPersistentUserCreatedAndAuthenticatedWhenCreateMissingUsersIsTrue()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function missingPersistentUserCreatedAndAuthenticatedWhenCreateMissingUsersIsCallable()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function missingPersistentUserCreatedAndAuthenticatedAndSyncedWhenCreateMissingUsersIsTrueAndSyncUserIsTrue()
    {
        $this->markTestIncomplete();
    }
}