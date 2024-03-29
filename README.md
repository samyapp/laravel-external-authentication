# Laravel External Authentication

Laravel authentication package that authenticates users based on HTTP request headers
or environment variables set by an external authentication source such as
Apache with basic authentication, SAML2 SSO via mod_auth_mellon, or a
custom implementation using Nginx's 
[http_auth_request](http://nginx.org/en/docs/http/ngx_http_auth_request_module.html).

This package focuses on _authenticating_ users and setting properties or attributes on
your user model based on those set via the external identity provider.

![Build Status](https://github.com/samyapp/laravel-external-authentication/actions/workflows/php.yml/badge.svg)

## Security

If you rely on HTTP headers to determine whether or not a user is authenticated
you *must* ensure that these headers have been sent to your Laravel app from a trusted source
and not spoofed by the client.

For example:

- Running Nginx with the 
  [http_auth_request module](http://nginx.org/en/docs/http/ngx_http_auth_request_module.html)
  setting the HTTP headers and proxying to a php-fpm backend running your application on 
  the same VM.
- Using Apache with [mod_auth_mellon](https://github.com/latchset/mod_auth_mellon)
  for SAML SSO with PHP on the same server, setting environment vars for PHP

Both of these cases should be safe, _provided you ensure the web servers
set the variables to blank values when no user is authenticated_.

In addition, if your authentication servers (Apache or Nginx in the examples above)
are proxying to php on one or more different servers (over a network) you should
ensure that php only responds to requests from those specific upstream servers to
avoid other users on the network being able to make requests with forged headers.

## Quickstart

1. Install: `composer require samyapp/laravel-external-authentication`
2. Publish the configuration: 
   ```
   php artisan vendor:publish --provider="SamYapp\LaravelExternalAuth\ExternalAuthServiceProvider"
   ```
3. Configure your application to use the External Guard:

   _config/auth.php_:
   ```
   'guards' => [
        'web' => [
            'driver' => 'external-auth',
            'provider' => 'users',
        ],
    ],
   ```
   
4. If using [transient users](#working-with--transient--users) in your app, 
   configure the user provider and model:

   _config/auth.php_:
   ```
   'providers' => [
        'users' => [
            'driver' => 'transient',
            'model' => '\SamYapp\LaravelExternalAuth\TransientUser',
        ],
    ],
   ```

5. Edit `config/external-auth.php` with your [configuration](#configuration).

6. Add authentication to the routes you want to protect.
7. Access the authenticated user the same way you would in any normal Laravel app.
   Any user attributes defined in your `config/external-auth.php` `'attributeMap'`
   should be available on your user model.

## Troubleshooting

When configuring an external authentication source such as Apache mod_mellon_auth it can be
useful to see what attributes and values it is sending to PHP.

You can enable logging of this data (using your app's Laravel logging configuration) by setting
`'logInput' => true,` in your config. You must also ensure that `'logLevel'` is set at least
to the minimum level that your app's logging level is (e.g. info, debug, warning, etc).

Be aware that this will (except when developmentMode = true) dump the contents of Request::server()
into your Laravel logs so it should only be enabled when essential for troubleshooting.

## Development / Testing Configuration

Configuring an authentication service such as Apache mod mellon
Instead of configuring an authentication service during development you can enable
development mode and specify the headers that you want set:

`config/external-auth.php`
```php
<?php

return [
    // ... other configuration options
    'attributeMap' => [
        'username' => 'X-USERNAME',
        'role' => 'X-USER-ROLE',
    ],
 
    'credentialAttributes' => [
        'username',
    ],
 
    'developmentMode' => true,
    'developmentAttributes' => [
        'X-USERNAME' => 'foo',
        'X-USER-ROLE' => 'admin',
        // .. any other attributes that would be set by your real auth provider...
    ]
]
```

The attribute names should be the same ones that would be set in your live environment.

In the example above the authenticated user would be one with a username of 'foo',
and the user model would have the 'role' property set to the value 'admin'.

## Authenticating Users

ExternalAuthGuard can work with your app's user model in one of the following ways:

1. Users exist within your app (the configured user provider can retrieve one that matches the credentials)
   and authentication fails if credential attributes do not match an existing user.
2. Users do not exist in your app, but a "Transient" user model should be created to represent
   the authenticated user for each request.

## Authorizing Users

Use the standard Laravel methods to authorize users for specific routes or actions.

## Configuration

### Working with your existing users and User model

ExternalAuthGuard uses the user provider and user model configured in your 
`config/auth.php` for the guard in the same way as the default Laravel `SessionGuard`,
calling `UserProvider::retrieveByCredentials()` with the values
(for example, `email`) from the server named in
the `config/external-auth.php` `credentialAttributes` setting.

#### Creating "missing" users

If you prefer to create users in your database "on-demand" when the guard authenticates them
for the first time, you can listen for the `UnknownUserAuthenticating` event which is dispatched
when all expected user attributes are present in the request but the user provider cannot
find an existing user.

You can create and persist the user in your listener similar to the example below 
(you might want to add some validation though):

_app/Providers/EventServiceProvider.php_
```php
    // class EventServiceProvider
    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Event::listen(function (UnknownUserAuthenticating $event) {
            $user = new \App\Models\User();
            foreach ($event->attributes as $name => $value) {
                $user->$name = $value;
            }
            $user->save();
            $event->guard->login($user);
        });
    }

```

Alternatively, you might want to log whenever a user is authenticated externally but does not
have a matching user account in your app.

#### Updating User Attributes

If you need to sync your apps' users table with the attribute values passed in from your external
authentication provider (for example, name, roles, or some other attributes) you can listen for
the `Illuminate\Auth\Events\Login` event which is fired when the guard's `login($user)` method
has been called.

This event will have the authenticated `User` model in its `$user` property
which will have the values of the properties set by the external authentication source.

For a standard Laravel User model, you can just call `$event->user->save()` to sync
it with your app database, e.g:

_app/Providers/EventServiceProvider.php_
```php
    // class EventServiceProvider
    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Event::listen(function (Illuminate\Auth\Events\Login $event) {
            $event->user->save();
        });
    }

```

### Working with "Transient" users

ExternalAuthGuard can be used for applications which do not have a separate users
database table, relying entirely on the external authentication source to set the
attributes that define a user.

You can use the provided [TransientUserProvider](src/TransientUserProvider.php) which is
automatically registered by the package service provider, and which accepts the attributes
passed in from the external authentication source and will return a user object with the values
of each attribute assigned to it without attempting to retrieve an existing user from a database.

The Transient user provider can be configured to use any class as the created user object.

The package includes a very simple [TransientUser object](src/TransientUser.php) which
just allows setting and getting attribute values but will work with any class which allows
setting and getting attributes on.

To configure the `TransientUserProvider` as the user provider for this guard,
edit your auth configuration:

_config/auth.php_
```

  'guards' => [
      'web' => [
         'driver'   => 'external-auth',
         'provider' => 'transient',
      ],
      
      // ... additional guard configuration

   ],

   'providers' => [
      'transient' => [
         'driver' => 'transient-user',
         'model'  => \SamYapp\LaravelExternalAuth\TransientUser::class,
      ],

      // ... other provider configuration
   ],

```

To use your own class (i.e. `App\Models\User`) for the authenticated user objects, just
set the `providers.transient.model` parameter as above, e.g.:

```php
   'providers' => [
      'transient' => [
         'driver' => 'transient-user',
         'model'  => \App\Models\User::class,
      ],

      // ... other provider configuration
   ],

```

### Additional Options

#### Custom Attribute Mapping

If you require additional logic to map the externally set attributes to your user model
you can create a callable and set the `mapAttributes` configuration setting to it.

See the [DefaultAttributeMapper](src/DefaultAttributeMapper.php) implementation for reference.

For example, create a `\My\App\MyAttributeMapper` class with a static 
`mapAttributes(AuthConfig $config, array $input): array` method, and in your `externa-auth.php`
config set:

_config/external-auth.php_
```php
return [
    // ... configuration...

    'mapAttributes' => '\My\App\MyAttributeMapper::mapAttributes',

    // ... more config
];
```

#### Authentication Guard Driver ID

To specify that your app should use the ExternalAuthGuard to authenticate its users
you specify `external-auth` as the name of the guard driver in your `config/auth.php`.

This is the name that the ExternalAuthServiceProvider registers the guard under by default.

If you need to use a different name you can set the `id` configuration option (ensuring you
also update the guards driver value to match).

_config/external-auth.php_
```php
return [    // ... configuration...

    'id' => 'my-external-auth',

    // ... more config
]; 
```