<?php

namespace Tests;

use Illuminate\Config\Repository;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Psr\Log\LoggerInterface;
use SamYapp\LaravelExternalAuth\ExternalAuthGuard;
use SamYapp\LaravelExternalAuth\ExternalAuthServiceProvider;
use SamYapp\LaravelExternalAuth\TransientUserProvider;

/**
 * @covers \SamYapp\LaravelExternalAuth\ExternalAuthServiceProvider
 * @covers \SamYapp\LaravelExternalAuth\AuthConfig::fromArray
 */
class ExternalAuthServiceProviderTest extends \Orchestra\Testbench\TestCase
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
            ExternalAuthServiceProvider::class,
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
        $app['config']->set('auth.guards.web.driver', 'external-auth');
        // define a default config, but allow overriding with already configured by @define-env
        $app['config']->set('external-auth', array_merge([
                'attributePrefix' => 'X-Test-',
            ],
            // may have already been partially defined by @define-env
            $app['config']->get('external-auth',[])
            )
        );
    }

    /**
     * @test
     */
    public function ServiceProviderRegistersGuard()
    {
        $this->assertInstanceOf(ExternalAuthGuard::class, auth()->guard('web'));
    }

    /**
     * @test
     */
    public function ServiceProviderPassesALoggerToTheGuard()
    {
        $provider = auth()->guard('web');
        $this->assertInstanceOf(LoggerInterface::class, $provider->logger);
    }

    protected function useTransientUserProvider($app)
    {
        $app['config']->set('auth.providers.users.driver', 'transient');
    }

    #[DefineEnvironment('useTransientUserProvider')]
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
        $app['config']->set('external-auth.developmentMode', true);
        $app['config']->set('external-auth.developmentAttributes', $this->developmentAttributes);
    }

    #[DefineEnvironment('enableDevelopmentMode')]
    public function ServiceProviderSetsInputToDevelopmentAttributesIfEnabled()
    {
        $this->assertEquals($this->developmentAttributes, auth()->guard('web')->input);
    }

    #[DefineEnvironment('enableDevelopmentMode')]
    public function exceptionThrownIfDevelopmentModeEnabledInProduction()
    {
        $this->expectException(\InvalidArgumentException::class);
        app()->detectEnvironment(fn() => 'production');
        app('auth')->user();
    }
}