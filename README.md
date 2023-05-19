# Laravel Remote Auth
Laravel authentication based on HTTP request headers
or environment variables set by an authenticating reverse proxy server such as
Apache with basic authentication, SAML2 SSO via mod_auth_mellon, or a
custom implementation using Nginx's 
[http_auth_request](http://nginx.org/en/docs/http/ngx_http_auth_request_module.html).

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

### Secure Token

To add an additional layer of security the RemoteGuard can require that
an additional header or environment variable gets set with a specific
secret value by the authentication server.

## Quickstart

1. Install: `composer require samyapp/laravel-remote-auth`
2. Publish the configuration: `php artisan publish`
4. Configure your application to use the Remote Guard:

   _config/auth.php_:
   ```
   'guards' => [
        'web' => [
            'driver' => 'remote-auth',
            'model' => App\User::class,
        ],
    ],
   ```
3. Edit `config/remote-auth.php`

## Authenticating Users

RemoteGuard can be configured to work with your app's user models in one of the following ways:

1. Users exist in your app (the configured user provider can retrieve one that matches the credentials)
   and authentication fails if credential attributes do not match an existing user.
2. Users should exist, but if the attributes do not match an existing user, a new one is created and
   authentication succeeds.
3. Users do not exist in your app, but a "Transient" user model should be created to represent
   the authentication attributes.

For (1) and (2), RemoteGuard can be configured to sync specific attributes in the app user
model with those provided by the authentication attributes (for example, SAML attributes passed
for name, email, phone, etc) if they differ from what was previously stored.

## Authorizing Users

You should use the standard Laravel methods to authorize users for specific routes or actions.

RemoteGuard also includes optional middleware to authorize users based on the presence, absence
or values of the attributes set on the user model.

## Configuration



### Persistent Users

RemoteGuard works with whatever Laravel user provider and user model are configured in your 
`config/auth.php` for the guard.

With the default configuration, the configured user provider _must_ be able to find a matching
user based on the credentials mapped by your configuration.

#### Creating non-existant users

Alternatively if you want to allow any user where the attributes have been set by the remote authenticator
@todo

#### Updating User Attributes

Attributes that should be updated in your user model should be listed in the `syncAttributes` 
configuration setting.

These _must_ match the names of one or more of the keys in the `expectedAttributes` option.

_e.g. config/remote-auth.php_

```
@todo
```

#### 

### Non-persistent User Model

If you do not require your authenticated users' data to be persisted (and retrieved) from
a database table or other storage, then you can use the 
[TransientUserProvider](src/TransientUserProvider.php) to create a
[TransientUser object](src/TransientUser.php) from the authenticated attributes.

[TransientUser](src/TransientUser.php) implements Laravel's AuthenticatableContract and AuthorizableContract
and simply takes a an array of attribute => value pairs in its constructor.

The `TransientUserProvider` user provider is registered by this package's
`RemoteAuthServiceProvider` class. To configure it as the provider for
this guard, edit your auth config:

_config/auth.php_
```

  'guards' => [
      'web' => [
         'driver'   => 'remote-auth',
         'provider' => 'transient',
      ],
      
      // ... additional guard configuration

   ],

   'providers' => [
      'transient' => [
         'driver' => 'transient-user',
         'model'  => \SamYapp\LaravelRemoteAuth\TransientUser::class,
      ],

      // ... other provider configuration
   ],

```

#### Adding additional functionality to TransientUser

You can configure the TransientUserProvider to create objects of your own subclass 
by setting the value for the `model` attribute in the provider configuration to the name
of your subclass, eg.

_config/auth.php_
```
  'providers' => [
      'transient' => [
         'driver' => 'transient-user',
         'model' => \App\Models\MyTransientUser::class,
      ],
      
      // ... other provider configuration
  ],
```
