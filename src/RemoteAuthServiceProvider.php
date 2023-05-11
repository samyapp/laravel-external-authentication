<?php

namespace SamYapp\LaravelRemoteAuth;

use App\Services\Auth\RemoteGuard;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\ServiceProvider;

class RemoteAuthServiceProvider extends ServiceProvider
{
    public function boot(AuthManager $auth, \Illuminate\Config\Repository $config): void
    {
        $this->publishes([
            __DIR__.'/../config/remote-auth.php' => config_path('remote-auth.php'),
        ]);
        $remoteAuthConfig = AuthConfig::fromArray($config->get('remote-auth') ?? []);
        // Register the custom guard driver
        $auth->extend($remoteAuthConfig->id, function ($app) use ($auth, $remoteAuthConfig) {
            return new RemoteAuthGuard($app[Session::class], $auth->createUserProvider('users'), $remoteAuthConfig);
        });
    }
}