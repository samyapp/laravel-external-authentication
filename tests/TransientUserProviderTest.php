<?php

namespace Tests;

use Illuminate\Foundation\Auth\User;
use SamYapp\LaravelRemoteAuth\TransientUser;
use SamYapp\LaravelRemoteAuth\TransientUserProvider;

/**
 * @covers SamYapp\LaravelRemoteAuth\TransientUserProvider
 * @covers SamYapp\LaravelRemoteAuth\TransientUser
 */
class TransientUserProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function constructorSetsTheModelPropertyFromItsParameter()
    {
        $model = TransientUser::class;
        $provider = new TransientUserProvider($model);
        $this->assertEquals($model, $provider->modelClass);
    }

    /**
     * @test
     */
    public function retrieveByCredentialsCreatesAnInstanceOfTheClassNamePassedToTheConstructor()
    {
        foreach ([TransientUser::class, User::class] as $className) {
            // works even if credentials are blank
            foreach ([[], ['foo' => 'bar']] as $credentials) {
                $provider = new TransientUserProvider($className);
                $created = $provider->retrieveByCredentials($credentials);
                $this->assertInstanceOf($className, $created);
            }
        }
    }

    /**
     * @test
     */
    public function retrieveByCredentialsAssignsItsAttributeParameterKeyValuePairsOnTheUserObject()
    {
        $provider = new TransientUserProvider(TransientUser::class);
        $credentials = ['foo' => 'bar', 'answer' => 42];
        $instance = $provider->retrieveByCredentials($credentials);
        foreach ($credentials as $name => $value) {
            $this->assertEquals($value, $instance->$name);
        }
    }
}