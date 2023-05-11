# Laravel Remote Auth
A Laravel authentication guard providing authentication based on HTTP request headers
or environment variables provided to the app by an authenticating reverse proxy,
for example Apache using basic auth or SAML2 SSO via mod_auth_mellon, or some
custom implementation using Nginx's 
[http_auth_request](http://nginx.org/en/docs/http/ngx_http_auth_request_module.html).

## Quickstart

1. `composer require samyapp/laravel-remote-auth`
2. `php artisan publish`
3. Edit `config/remote-auth.php` 
4. Edit `config/auth.php` and set the guard:
   ```
   'guards' => [
        'web' => [
            'driver' => 'remote-auth',
            'model' => App\User::class,
        ],
    ],
   ```
