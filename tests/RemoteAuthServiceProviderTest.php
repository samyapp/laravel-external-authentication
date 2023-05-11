<?php

namespace Tests;

use SamYapp\LaravelRemoteAuth\RemoteAuthGuard;
use SamYapp\LaravelRemoteAuth\RemoteAuthServiceProvider;

/**
 * @covers \SamYapp\LaravelRemoteAuth\RemoteAuthServiceProvider
 */
class RemoteAuthServiceProviderTest extends \Orchestra\Testbench\TestCase
{
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
        // Setup default database to use sqlite :memory:
        $app['config']->set('auth.guards.web.driver', 'remote-auth');
        $app['config']->set('remote-auth', [
            'createMissingUsers' => true,
            'attributePrefix' => 'X-Test-',
        ]);
    }

    /**
     * @test
     */
    public function ServiceProviderRegistersGuard()
    {
        $this->assertInstanceOf(RemoteAuthGuard::class, auth()->guard('web'));
    }
}