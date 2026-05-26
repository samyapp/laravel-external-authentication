<?php

namespace SamYapp\LaravelExternalAuth;

use Illuminate\Auth\AuthManager;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class ExternalAuthServiceProvider extends ServiceProvider
{
    public function boot(AuthManager $auth, Repository $config, LoggerInterface $logger): void
    {
        $this->publishes([
            __DIR__.'/../config/external-auth.php' => config_path('external-auth.php'),
        ]);

        // Register the custom user provider driver
        $auth->provider('transient', function ($app, array $providerConfig) {
            $externalAuthConfig = AuthConfig::fromArray($app['config']->get('external-auth') ?? []);
            return new TransientUserProvider(
                $providerConfig['model'] ?? TransientUser::class,
                $externalAuthConfig->authIdentifierName
            );
        });

        // Register the custom guard driver
        $auth->extend('external-auth', function (Application $app, string $name) use ($auth, $logger) {
            // Read config dynamically on each guard instantiation
            $externalAuthConfig = AuthConfig::fromArray($app['config']->get('external-auth') ?? []);
            
            // Cannot run developmentMode in production
            if ($externalAuthConfig->developmentMode && $app->environment('production')) {
                throw new \InvalidArgumentException(
                    'Authentication development mode must not be enabled in a production environment.'
                );
            }
            $input = $externalAuthConfig->developmentMode
                ? $externalAuthConfig->developmentAttributes
                : $app[Request::class]->server();
            
            return new ExternalAuthGuard(
                $app,
                $auth->createUserProvider($externalAuthConfig->userProvider),
                $input,
                $app->get(Dispatcher::class),
                $name,
                $logger,
            );
        });
    }
}