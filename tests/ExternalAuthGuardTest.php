<?php

namespace Tests;

use App\Models\User;
use Illuminate\Auth\DatabaseUserProvider;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use SamYapp\LaravelExternalAuth\AuthConfig;
use SamYapp\LaravelExternalAuth\ExternalAuthGuard;
use SamYapp\LaravelExternalAuth\ExternalAuthServiceProvider;
use SamYapp\LaravelExternalAuth\TransientUser;
use SamYapp\LaravelExternalAuth\TransientUserProvider;

/**
 * @covers \SamYapp\LaravelExternalAuth\ExternalAuthGuard
 * @covers \SamYapp\LaravelExternalAuth\AuthConfig
 */
class ExternalAuthGuardTest extends \Orchestra\Testbench\TestCase
{
    protected $developmentAttributes = ['foo' => 'bar', 'one' => 'two'];

    protected function getPackageProviders($app)
    {
        return [
            ExternalAuthServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('auth.guards.web.driver', 'external-auth');
        // define a default config, but allow overriding with already configured by @define-env
        $app['config']->set('external-auth', array_merge([],
                // may have already been partially defined by @define-env
                $app['config']->get('external-auth',[])
            )
        );
    }

    /**
     * @test
     */
    public function constructorAssignsInputsToProperties()
    {
        $config = AuthConfig::fromArray(['attributeMap' => ['something' => 'somethingElse', 'one', '42']]);
        $provider = new TransientUserProvider(TransientUser::class);
        $input = ['one' => 'two', 'three' => 'four'];
        $dispatcher = app(Dispatcher::class);
        $guardName = 'external-foo';
        $guard = new ExternalAuthGuard($config, $provider, $input, $dispatcher, $guardName);
        $this->assertEquals($config, $guard->config);
        $this->assertEquals($provider, $guard->getProvider());
        $this->assertEquals($input, $guard->input);
        $this->assertEquals($dispatcher, $guard->dispatcher);
        $this->assertEquals($guardName, $guard->guardName);
    }

    /**
     * @test
     */
    public function constructorSetsConfigUserProviderAndRequest()
    {
        $auth = app('auth');
        $config = AuthConfig::fromArray(['id' => 'test-external-auth']);
        $provider = $auth->getProvider('database');
        $input = ['foo' => 'bar'];
        $guard = new ExternalAuthGuard($config, $provider, $input, app(Dispatcher::class));
        $this->assertEquals($config, $guard->config);
        $this->assertEquals($provider, $guard->getProvider());
        $this->assertEquals($input, $guard->input);
    }

    /**
     * @test
     */
    public function userReturnsTheAuthenticatedUserForMultipleCallsOnTheSameRequest()
    {
        $auth = app('auth');
        $config = AuthConfig::fromArray(['id' => 'external-auth']);
        $provider = $auth->getProvider('users');
        $guard = new ExternalAuthGuard($config, $provider, [], app(Dispatcher::class));
        $user = new TransientUser(['foo' => 'bar']);
        // set the authenticated user
        $guard->setUser($user);
        $this->assertEquals($user, $guard->user());
        $this->assertEquals($user, $guard->user());
    }

    /**
     * @test
     */
    public function userReturnsNullIfNoAuthenticationPresent()
    {
        $this->assertNull(app('auth')->guard('web')->user());
    }
    
    /**
     * Configure app to use TransientUserProvider
     */
    protected function configureTransientUserConfig($app)
    {
        $app['config']->set('auth.providers.users.driver', 'transient');
        $app['config']->set('auth.providers.users.model', TransientUser::class);
        $app['config']->set('external-auth.developmentMode', true);
        // set the "fake" attributes for dev mode
        $app['config']->set('external-auth.developmentAttributes', $this->developmentAttributes);
        // set the attributes we expect to be present
        $app['config']->set('external-auth.attributeMap', array_keys($this->developmentAttributes));
        // set the attributes used for credentials
        $app['config']->set('external-auth.credentialAttributes', array_keys($this->developmentAttributes));
    }

    /**
     * @test
     * @define-env configureTransientUserConfig
     */
    public function userReturnsTransientUserWhenConfiguredAndAttributesPresent()
    {
        $guard = app('auth')->guard();
        $user = $guard->user();
        $this->assertInstanceOf(TransientUser::class, $user);
        foreach ($this->developmentAttributes as $key => $value) {
            $this->assertEquals($value, $user->$key);
        }
    }

    /**
     * @test
     */
    public function setAttributesSetsPropertiesOnAuthenticatableObject()
    {
        $guard = new ExternalAuthGuard(
            AuthConfig::fromArray([]),
            app('auth')->guard()->getProvider(),
            [],
            app(Dispatcher::class)
        );
        $user = new TransientUser();
        $attrs = ['name' => 'foo', 'email' => 'test@example.com', 'username' => 'bar'];
        $guard->setAttributes($user, $attrs);
        foreach ($attrs as $name => $value) {
            $this->assertEquals($value, $user->$name);
        }
    }

    /**
     * @test
     */
    public function getMissingRequiredAttributesReturnsEmptyArrayWhenNoAttributesAreRequired()
    {
        $config = AuthConfig::fromArray([
           'attributeMap' => [
               'name' => ['required' => false],
               'email' => ['required' => false],
               'username' => ['required' => false],
            ]
        ]);
        $input = ['name' => 'foo', 'username' => 'bar'];
        $guard = new ExternalAuthGuard($config, app('auth')->guard()->getProvider(), $input, app(Dispatcher::class));
        $expected = [];
        $this->assertEquals($expected, $guard->getMissingRequiredAttributes($config, $input));
    }

    /**
     * @test
     */
    public function getMissingRequiredAttributesReturnsEmptyArrayWhenRequiredAttributesArePresent()
    {
        $config = AuthConfig::fromArray([
            'attributeMap' => [
                'name' => ['required' => true],
                'email' => ['required' => true],
                'username' => ['required' => false],
            ]
        ]);
        $input = ['name' => 'foo', 'email' => 'bar'];
        $guard = new ExternalAuthGuard($config, app('auth')->guard()->getProvider(), $input, app(Dispatcher::class));
        $expected = [];
        $this->assertEquals($expected, $guard->getMissingRequiredAttributes($config, $input));
    }

    /**
     * @test
     */
    public function getMissingRequiredAttributesReturnsAttributesWhichAreMissingFromInput()
    {
        $config = AuthConfig::fromArray([
            'attributeMap' => [
                'name' => ['required' => true],
                'email' => ['required' => false],
                'username' => ['required' => true],
            ]
        ]);
        $input = ['name' => 'foo','email' => 'a@example.com'];
        $guard = new ExternalAuthGuard($config, app('auth')->guard()->getProvider(), $input, app(Dispatcher::class));
        $expected = ['username' => $config->attributeMap['username']];
        $this->assertEquals($expected, $guard->getMissingRequiredAttributes($config, $input));
    }

    /**
     * @test
     */
    public function validateReturnsFalseEvenIfItsInputContainsCredentialAttributes()
    {
        $config = AuthConfig::fromArray([
            'attributeMap' => ['username'],
            'credentialAttributes' => ['username'],
        ]);
        $credentials = $input = ['username' => 'foo'];
        $guard = new ExternalAuthGuard($config, app('auth')->guard()->getProvider(), $input, app(Dispatcher::class));
        $this->assertFalse($guard->validate($credentials));
    }

    /**
     * @test
     */
    public function loginSetsTheUserToTheGivenUser()
    {
        $guard = new ExternalAuthGuard(AuthConfig::fromArray([]), app('auth')->guard()->getProvider(), [], app(Dispatcher::class));
        $user = new TransientUser(['foo' => 'bar']);
        $guard->login($user);
        $this->assertEquals($user, $guard->user());
    }

    /**
     * @test
     */
    public function setUserSetsTheUserToTheGivenUserAndDispatchesAuthenticatedEvent()
    {
        Event::fake();
        $guard = new ExternalAuthGuard(AuthConfig::fromArray([]), app('auth')->guard()->getProvider(), [], app(Dispatcher::class));
        $user = new TransientUser(['foo' => 'bar']);
        $guard->setUser($user);
        $this->assertEquals($user, $guard->user());
        Event::assertDispatched(Authenticated::class, function (Authenticated $event) use($user, $guard) {
           return $event->guard == $guard->guardName && $user = $event->user;
        });
    }

    /**
     * @test
     * @define-env configureTransientUserConfig
     */
    public function loginWorksAgainAfterALogout()
    {
        $guard = app('auth')->guard();
        $user = $guard->user();
        $this->assertInstanceOf(TransientUser::class, $user);
        $guard->logout();
        $guard->login($user);
        // ensure that user can be logged in again after being logged out
        $this->assertEquals($user, $guard->user());
    }

    /**
     * @test
     * @define-env configureTransientUserConfig
     */
    public function logoutUnsetsTheUserAndEnsuresUserReturnsNullForRestOfRequest()
    {
        $guard = app('auth')->guard();
        $user = $guard->user();
        $this->assertInstanceOf(TransientUser::class, $user);
        $guard->logout();
        // ensure that user stays logged out for the request
        $this->assertNull($guard->user());
    }
}