<?php

namespace Tests;

use Illuminate\Config\Repository;
use SamYapp\LaravelRemoteAuth\RemoteAuthGuard;
use SamYapp\LaravelRemoteAuth\RemoteAuthServiceProvider;
use SamYapp\LaravelRemoteAuth\TransientUserProvider;

/**
 * @covers \SamYapp\LaravelRemoteAuth\RemoteAuthServiceProvider
 */
class RemoteAuthServiceProviderTest extends \Orchestra\Testbench\TestCase
{
    protected $developmentAttributes = ['foo' => 'bar', 'one' => 'two'];
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
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
                'createMissingUsers' => true,
                'attributePrefix' => 'X-Test-',
            ],
            // may have already been partially defined by @define-env
            $app['config']->get('remote-auth',[])
            )
        );
    }

    /**
     * @test
     */
    public function ServiceProviderRegistersGuard()
    {
        $this->assertInstanceOf(RemoteAuthGuard::class, auth()->guard('web'));
    }

    protected function useTransientUserProvider($app)
    {
        $app['config']->set('auth.providers.users.driver', 'transient');
    }

    /**
     * @test
     * @define-env useTransientUserProvider
     */
    public function ServiceProviderRegistersTransientUserProvider()
    {
        $this->assertInstanceOf(TransientUserProvider::class, auth()->getProvider());
    }

    /** @test */
    public function ServiceProviderSetsInputToServerVarsByDefault()
    {
        $this->assertEquals(app('request')->server(), auth()->guard('web')->input);
    }

    protected function enableDevelopmentMode($app)
    {
        $app['config']->set('remote-auth.developmentMode', true);
        $app['config']->set('remote-auth.developmentAttributes', $this->developmentAttributes);
    }

    /**
     * @test
     * @define-env enableDevelopmentMode
     */
    public function ServiceProviderSetsInputToDevelopmentAttributesIfEnabled()
    {
        $this->assertEquals($this->developmentAttributes, auth()->guard('web')->input);
    }
}