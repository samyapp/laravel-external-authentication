<?php

namespace SamYapp\LaravelExternalAuth;

use App\Services\Auth\ExternalGuard;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\ServiceProvider;

class ExternalAuthServiceProvider extends ServiceProvider
{
    public function boot(AuthManager $auth, \Illuminate\Config\Repository $config): void
    {
        $this->publishes([
            __DIR__.'/../config/external-auth.php' => config_path('external-auth.php'),
        ]);
        $externalAuthConfig = AuthConfig::fromArray($config->get('external-auth') ?? []);

        // register our provider which we use
        $auth->provider('transient', function ($app, array $config) {
           return new TransientUserProvider($config['model'] ?? TransientUser::class); 
        });

        // Register the custom guard driver
        $auth->extend($externalAuthConfig->id, function (Application $app, string $name) use ($auth, $externalAuthConfig) {
            // Cannot run developmentMode in production
            if ($externalAuthConfig->developmentMode && $app->environment('production')) {
                throw new \InvalidArgumentException(
                    'Authentication development mode must not be enabled in a production environment.'
                );
            }
            $input = $externalAuthConfig->developmentMode
                ? $externalAuthConfig->developmentAttributes
                : $app[Request::class]->server();
            if ($externalAuthConfig->logInput) {
                $app[Logger::class]?->log($externalAuthConfig->logLevel ?? 'info', 'External authentication input', $input);
            }
            return new ExternalAuthGuard(
                $externalAuthConfig,
                $auth->createUserProvider($externalAuthConfig->userProvider),
                $input,
                $app->get(Dispatcher::class),//dispatcher,
                $name
            );
        });
    }
}