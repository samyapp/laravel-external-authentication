<?php

namespace Tests;

use App\Models\User;
use Illuminate\Auth\DatabaseUserProvider;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use SamYapp\LaravelRemoteAuth\AuthConfig;
use SamYapp\LaravelRemoteAuth\RemoteAuthGuard;
use SamYapp\LaravelRemoteAuth\RemoteAuthServiceProvider;
use SamYapp\LaravelRemoteAuth\TransientUser;

/**
 * @covers \SamYapp\LaravelRemoteAuth\RemoteAuthGuard
 */
class RemoteAuthGuardTest extends \Orchestra\Testbench\TestCase
{
    protected $developmentAttributes = ['foo' => 'bar', 'one' => 'two'];

    protected function getPackageProviders($app)
    {
        return [
            RemoteAuthServiceProvider::class,
        ];
    }    
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
        $auth = app('auth');
        $config = AuthConfig::fromArray(['id' => 'remote-auth']);
        $provider = $auth->getProvider('users');
        $guard = new RemoteAuthGuard($config, $provider, [], app(Logger::class));
        $user = new TransientUser(['foo' => 'bar']);
        // set the authenticated user
        $guard->setUser($user);
        $this->assertEquals($user, $guard->user());
        $this->assertEquals($user, $guard->user());
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
//                'attributePrefix' => 'X-Test-',
            ],
                // may have already been partially defined by @define-env
                $app['config']->get('remote-auth',[])
            )
        );
    }

    /**
     * @test
     */
    public function userReturnsNullIfNoAuthenticationPresent()
    {
        $this->assertNull(app('auth')->guard('web')->user());
    }


    protected function configureTransientUserConfig($app)
    {
        $app['config']->set('auth.providers.users.driver', 'transient');
        $app['config']->set('auth.providers.users.model', TransientUser::class);
        $app['config']->set('remote-auth.developmentMode', true);
        // set the "fake" attributes for dev mode
        $app['config']->set('remote-auth.developmentAttributes', $this->developmentAttributes);
        // set the attributes we expect to be present
        $app['config']->set('remote-auth.expectedAttributes', array_keys($this->developmentAttributes));
        // set the attributes used for credentials
        $app['config']->set('remote-auth.credentialAttributes', array_keys($this->developmentAttributes));
    }

    /**
     * @test
     * @define-env configureTransientUserConfig
     */
    public function userReturnsTransientUserWhenConfiguredAndAttributesPresent()
    {
        $guard = app('auth')->guard();
        $user = $guard->user();
        $this->assertInstanceOf(TransientUser::class, $user);
        foreach ($this->developmentAttributes as $key => $value) {
            $this->assertEquals($value, $user->$key);
        }
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
}