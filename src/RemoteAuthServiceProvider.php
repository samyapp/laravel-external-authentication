<?php

namespace SamYapp\LaravelRemoteAuth;

use App\Services\Auth\RemoteGuard;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\ServiceProvider;

class RemoteAuthServiceProvider extends ServiceProvider
{
    public function boot(AuthManager $auth, \Illuminate\Config\Repository $config, Dispatcher $dispatcher): void
    {
        $this->publishes([
            __DIR__.'/../config/remote-auth.php' => config_path('remote-auth.php'),
        ]);
        $remoteAuthConfig = AuthConfig::fromArray($config->get('remote-auth') ?? []);

        // register our provider which we use
        $auth->provider('transient', function ($app, array $config) {
           return new TransientUserProvider($config['model'] ?? TransientUser::class); 
        });

        // Register the custom guard driver
        $auth->extend($remoteAuthConfig->id, function ($app, string $name) use ($auth, $remoteAuthConfig, $dispatcher) {
            return new RemoteAuthGuard(
                $remoteAuthConfig,
                $auth->createUserProvider($remoteAuthConfig->userProvider),
                $remoteAuthConfig->developmentMode
                    ? $remoteAuthConfig->developmentAttributes
                    : $app[Request::class]->server(),
                $dispatcher,
                $name
            );
        });
    }
}