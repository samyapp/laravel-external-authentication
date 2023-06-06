<?php

namespace Tests;

use App\Models\User;
use Illuminate\Auth\DatabaseUserProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use SamYapp\LaravelRemoteAuth\AuthConfig;
use SamYapp\LaravelRemoteAuth\DefaultUserCreator;
use SamYapp\LaravelRemoteAuth\Events\IncompleteAuthenticationAttributes;
use SamYapp\LaravelRemoteAuth\Events\UnknownUserAuthenticating;
use SamYapp\LaravelRemoteAuth\RemoteAuthGuard;
use SamYapp\LaravelRemoteAuth\RemoteAuthServiceProvider;
use SamYapp\LaravelRemoteAuth\TransientUser;
use SamYapp\LaravelRemoteAuth\TransientUserProvider;
use Tests\Support\TestUser;

/**
 * @covers \SamYapp\LaravelRemoteAuth\RemoteAuthGuard
 */
class RemoteAuthGuardFeatureTest extends \Orchestra\Testbench\TestCase
{
    protected $developmentAttributes = ['foo' => 'bar', 'one' => 'two'];

    const TEST_EMAIL = 'test@example.com';
    const TEST_USER_NAME = 'Test User';
    const ADMIN_ROLE = 'admin';
    const USER_ROLE = 'user';
    const TEST_ROLES = ['admin', 'user'];

    /** @var string - used in defineEnvironment */
    protected string $userModel = TestUser::class;

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }

    protected function getPackageProviders($app)
    {
        return [
            RemoteAuthServiceProvider::class,
        ];
    }

    /**
     * Define default environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // These will override the orchestral/testbench laravel app defaults
        // config/auth.php
        $app['config']->set('auth.guards.web.driver', 'remote-auth');
        $app['config']->set('auth.providers.users.model', $this->userModel);

        // define a default config, but allow overriding with already configured by @define-env
        // config/remote-auth.php
        $defaults = [
            'attributePrefix' => 'X-TESTING-',
            'attributeMap' => [
                'email' => 'UID',
                'name' => 'DISPLAY-NAME',
                // attributes where the remote is a regex become arrays
                'roles' => ['remote' => 'ROLE-.*', 'required' => true],
            ],
            'credentialAttributes' => ['email'],
            'developmentMode' => true,
            'developmentAttributes' => [
                'X-TESTING-UID' => static::TEST_EMAIL,
                'X-TESTING-DISPLAY-NAME' => static::TEST_USER_NAME,
                'X-TESTING-ROLE-0' => static::ADMIN_ROLE,
                'X-TESTING-ROLE-1' => static::USER_ROLE,
            ],
        ];
        $app['config']->set('remote-auth', array_merge($defaults, $app['config']->get('remote-auth',[])));
    }

    public function configureTransientUserProviderWithDefaultUserModel($app)
    {
        // config/auth.php
        $app['config']->set('auth.providers.users.driver', 'transient');
        Event::fake();
    }

    /**
     * @test
     * @define-env configureTransientUserProviderWithDefaultUserModel
     */
    public function transientUserAuthenticatesWithCorrectAttributesAndDispatchesLoginEvent()
    {
        $user = app('auth')->user();
        $guard = app('auth')->guard();
        $this->assertInstanceOf(RemoteAuthGuard::class, $guard);
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
        $this->assertEquals(static::TEST_ROLES, $user->roles);

        Event::assertDispatched(function (Login $event) use ($guard, $user) {
            return ($guard->guardName === $event->guard)
                && ($user === $event->user);
        },1);
        Event::assertNotDispatched(IncompleteAuthenticationAttributes::class);
        Event::assertNotDispatched(UnknownUserAuthenticating::class);
    }

    public function configureTransientUserProviderWithTransientUserModel($app)
    {
        // config/auth.php
        $app['config']->set('auth.providers.users.driver', 'transient');
        $this->userModel = TransientUser::class; // will be used in defineEnvironment
        Event::fake();
    }

    /**
     * @test
     * @define-env configureTransientUserProviderWithTransientUserModel
     */
    public function transientUserWithTransientUserModelAuthenticatesWithCorrectAttributesAndDispatchesLoginEvent()
    {
        $user = app('auth')->user();
        $guard = app('auth')->guard();
        $this->assertInstanceOf(TransientUser::class, $user);
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
        $this->assertEquals(static::TEST_ROLES, $user->roles);

        Event::assertDispatched(function (Login $event) use ($guard, $user) {
            return ($guard->guardName === $event->guard)
                && ($user === $event->user);
        },1);
        Event::assertNotDispatched(IncompleteAuthenticationAttributes::class);
        Event::assertNotDispatched(UnknownUserAuthenticating::class);
    }

    protected function configureEventFaking()
    {
        Event::fake();
    }

    /**
     * @test
     * @define-env configureEventFaking
     */
    public function existingUserAuthenticatesWithCorrectAttributesAndDispatchesLoginEvent()
    {
        // create a user so there is one to retrieve
        $user = new TestUser;
        $user->email = static::TEST_EMAIL;
        // start them off with a name different from the attributes
        $originalName = 'name-that-will-change';
        $user->name = $originalName;
        $user->save();

        $user = app('auth')->user();
        $guard = app('auth')->guard();
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
        $this->assertEquals(static::TEST_ROLES, $user->roles);

        Event::assertDispatched(function (Login $event) use ($guard, $user) {
            return ($guard->guardName === $event->guard)
                && ($user === $event->user);
        },1);
        Event::assertNotDispatched(IncompleteAuthenticationAttributes::class);
        Event::assertNotDispatched(UnknownUserAuthenticating::class);
    }

    /**
     * @test
     */
    public function missingUserCreatedByEventListenerIsReturnedByUser()
    {
        // user should not exist
        $this->assertNull(app('auth')->user());
        // add an event listener for the event that creates and logs in the user
        Event::listen(UnknownUserAuthenticating::class, function (UnknownUserAuthenticating $event) {
           $user = new TestUser();
           $user->email = $event->attributes['email'];
           $user->name = $event->attributes['name'];
           $user->roles = $event->attributes['roles'];
           $user->save();
           $event->guard->login($user);
        });
        // redo authentication
        $user = app('auth')->user();
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
        $this->assertEquals(static::TEST_ROLES, $user->roles);
    }
}