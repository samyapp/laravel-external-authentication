<?php

return [
    //
    // optional prefix that all remotely set attributes will have as part of their name, e.g. 'MELLON_'
    //
    'attributePrefix' => '',

    //
    // 'attributeMap' contains the list of attributes that should be taken from those set in the request headers or
    // environment variables by the authentication source and mapped onto your user model.
    // Each element in the array takes the form:
    //
    //      [ $propertyName => [ 'remote' => $remoteAttribute, 'required' => $required ] ... ],
    //        Where:    $propertyName is the name of the attribute on your user model.
    //                  $remoteAttribute is the name of a single attribute or a regular expression matching zero or
    //                      more attributes provided by the authentication source.
    //                  $required is either true or false and indicates whether the attribute MUST be present.
    //
    // For example, if you are using Apache mod_mellon and it sets the environment variables:
    //  - 'MELLON_MAIL' which corresponds to your user model's 'email'
    //  - 'MELLON_NAME' which corresponds to your user model's 'name'
    //  - 'MELLON_ROLE_0', 'MELLON_ROLE_1', etc which correspond to the names of roles your user has, but is optional
    //
    // Then your attributeMap would look something like:
    // 'attributeMap' => [
    //      'email' => ['remote' => 'MELLON_MAIL', 'required' => true],
    //      'name' => ['remote' => 'MELLON_NAME', 'required' => true],
    //      'roles' => ['remote' => 'MELLON_ROLE_.*', 'required' => false],
    // ],
    //
    // If your attribute is not optional, you can use the shorthand ['mail' => 'MELLON_MAIL'] and if an attribute is
    // required and also  has the same name on both the authentication provider and your app and is required,
    // then you can just list the name, e.g. [ 'mail' ].
    //
    // Authentication fails if any required attributes are not set and an IncompleteAuthenticationAttributes event is
    // dispatched that your app can listen to in order to help detect configuration or data issues.
    //
    'attributeMap' => [
    //    'email' => [ 'remote' => 'MELLON_MAIL', 'required' => true],
    ],

    //
    // One or more attributes from the attributeMap that will be used by the user provider's retrieveByCredentials()
    // method to identify the user model that is being authenticated.
    //
    'credentialAttributes' => [
    //    'email',
    ],

    //
    // When developing locally, for example with Laravel Sail, or running tests in a CI environment
    // it may not be possible to have a configured authentication source to provide your attributes.
    //
    // If 'developmentMode' => true, then the RemoteAuthServiceProvider will use the attributes defined in the
    // 'developmentAttributes' array below instead of looking for server environment variables or request headers.
    //
    'developmentAttributes' => [
        'TESTING_USERNAME' => env('REMOTE_AUTH_TESTING_USERNAME', 'sam'),
        'TESTING_EMAIL' => env('REMOTE_AUTH_TESTING_EMAIL', 'sam@example.com'),
    ],

    //
    // Enable development mode (uses the array of developmentAttributes instead of server environment variables or
    // request headers).
    //
    'developmentMode' => env('REMOTE_AUTH_DEV_MODE', false),

    //
    // Name of the UserProvider to use to retrieve users. The name is one of the keys in the providers array
    // in your app's config/auth.php file. The default 'users' is the name of default user provider in a
    // Laravel app which defaults to being the eloquent user provider.
    //
    // You probably only need to change this is you have multiple user providers configured for different
    // parts of your app.
    //
    'userProvider' => 'users',

    //
    // Optional callable which takes an AuthConfig object (matching this file), and an array of attributes
    // ($request->server() or 'developmentAttributes' if 'developmentMode' is true) and returns an array
    // where the keys are the attribute names for your user model and the values are the matching ones from
    // the input array.
    //
    // You will only need this if the default attribute mapping is not sufficient. See DefaultAttributeMapper
    // for the default implementation (when the value below is null).
    //
    'mapAttributes' => null,

    //
    // The name to register this authentication guard driver under. You probably don't need to change this.
    //
    'id' => 'remote-auth',
];
