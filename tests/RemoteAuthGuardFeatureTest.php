<?php

namespace Tests;

use App\Models\User;
use Illuminate\Auth\DatabaseUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use SamYapp\LaravelRemoteAuth\AuthConfig;
use SamYapp\LaravelRemoteAuth\DefaultUserCreator;
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
            'createMissingUsers' => false,
            'developmentMode' => true,
            'developmentAttributes' => [
                'X-TESTING-UID' => static::TEST_EMAIL,
                'X-TESTING-DISPLAY-NAME' => static::TEST_USER_NAME,
                'X-TESTING-ROLE-0' => static::ADMIN_ROLE,
                'X-TESTING-ROLE-1' => static::USER_ROLE,
            ],
            'syncUser' => false,
        ];
        $app['config']->set('remote-auth', array_merge($defaults, $app['config']->get('remote-auth',[])));
    }

    public function configureTransientUserProviderWithDefaultUserModel($app)
    {
        // config/auth.php
        $app['config']->set('auth.providers.users.driver', 'transient');
    }

    /**
     * @test
     * @define-env configureTransientUserProviderWithDefaultUserModel
     */
    public function transientUserAuthenticatesWithCorrectAttributes()
    {
        $user = app('auth')->user();
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
        $this->assertEquals(static::TEST_ROLES, $user->roles);
    }

    public function configureTransientUserProviderWithTransientUserModel($app)
    {
        // config/auth.php
        $app['config']->set('auth.providers.users.driver', 'transient');
        $this->userModel = TransientUser::class; // will be used in defineEnvironment
    }

    /**
     * @test
     * @define-env configureTransientUserProviderWithTransientUserModel
     */
    public function transientUserWithTransientUserModelAuthenticatesWithCorrectAttributes()
    {
        $user = app('auth')->user();
        $this->assertInstanceOf(TransientUser::class, $user);
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
        $this->assertEquals(static::TEST_ROLES, $user->roles);
    }

    /**
     * @test
     */
    public function existingPersistentUserAuthenticatesWithCorrectAttributesButDoesNotSyncWhenSyncUserIsFalse()
    {
        $user = app('auth')->user();
        $this->assertNull($user);

        // create a user so there is one to retrieve
        $user = new TestUser;
        $user->email = static::TEST_EMAIL;
        // start them off with a name different from the attributes
        $originalName = 'name-that-will-change';
        $user->name = $originalName;
        $user->save();

        $user = app('auth')->user();
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
        $this->assertEquals(static::TEST_ROLES, $user->roles);

        // check the changes haven't been persisted
        $user->refresh();
        $this->assertEquals($originalName, $user->name);
    }

    protected function configurePersistentUserToSync($app)
    {
        $app['config']->set('remote-auth.syncUser', true);
    }

    /**
     * @test
     * @define-env configurePersistentUserToSync
     */
    public function existingPersistentUserAuthenticatesWithCorrectAttributesAndSyncsWhenSyncUserIsTrue()
    {
        $user = app('auth')->user();
        $this->assertNull($user);

        // create a user so there is one to retrieve
        $user = new TestUser;
        $user->email = static::TEST_EMAIL;
        // start them off with a name different from the attributes
        $originalName = 'name-that-will-change';
        $user->name = $originalName;
        $user->save();

        $user = app('auth')->user();
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
        $this->assertEquals(static::TEST_ROLES, $user->roles);

        // check the changes have been persisted
        $user->refresh();
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
    }

    protected function configurePersistentUserToSyncWithCallable($app)
    {
        $app['config']->set('remote-auth.syncUser', fn (Authenticatable $user, array $attrs, AuthConfig $config) => $user->save());
    }

    /**
     * @test
     * @define-env configurePersistentUserToSyncWithCallable
     */
    public function existingPersistentUserAuthenticatesWithCorrectAttributesAndSyncsWhenSyncUserIsCallable()
    {
        $user = app('auth')->user();
        $this->assertNull($user);

        // create a user so there is one to retrieve
        $user = new TestUser;
        $user->email = static::TEST_EMAIL;
        // start them off with a name different from the attributes
        $originalName = 'name-that-will-change';
        $user->name = $originalName;
        $user->save();

        $user = app('auth')->user();
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
        $this->assertEquals(static::TEST_ROLES, $user->roles);

        // check the changes have been persisted
        $user->refresh();
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
    }

    protected function configurePersistentUserWithCreateMissingUsersTrue($app)
    {
        $app['config']->set('remote-auth.createMissingUsers', true);
        $app['config']->set('remote-auth.userModel', TestUser::class);
    }

    /**
     * @test
     * @define-env configurePersistentUserWithCreateMissingUsersTrue
     */
    public function missingPersistentUserCreatedAndAuthenticatedWhenCreateMissingUsersIsTrue()
    {
        $user = app('auth')->user();
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
        $this->assertEquals(static::TEST_ROLES, $user->roles);

        // check the changes have been persisted
        $user->refresh();
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
    }

    protected function configurePersistentUserWithCreateMissingUsersCallback($app)
    {
        $app['config']->set('remote-auth.createMissingUsers', new DefaultUserCreator());
        $app['config']->set('remote-auth.userModel', TestUser::class);
    }

    /**
     * @test
     * @define-env configurePersistentUserWithCreateMissingUsersCallback
     */
    public function missingPersistentUserCreatedAndAuthenticatedWhenCreateMissingUsersIsCallable()
    {
        $user = app('auth')->user();
        $this->assertInstanceOf(TestUser::class, $user);
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
        $this->assertEquals(static::TEST_ROLES, $user->roles);

        // check the changes have been persisted
        $user->refresh();
        $this->assertEquals(static::TEST_EMAIL, $user->email);
        $this->assertEquals(static::TEST_USER_NAME, $user->name);
    }
}