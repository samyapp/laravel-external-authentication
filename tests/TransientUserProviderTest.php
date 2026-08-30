<?php

namespace Tests;

use Illuminate\Foundation\Auth\User;
use SamYapp\LaravelExternalAuth\TransientUser;
use SamYapp\LaravelExternalAuth\TransientUserProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\SamYapp\LaravelExternalAuth\TransientUserProvider::class)]
#[CoversClass(\SamYapp\LaravelExternalAuth\TransientUser::class)]
class TransientUserProviderTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function constructorSetsTheModelPropertyFromItsParameter()
    {
        $model = TransientUser::class;
        $provider = new TransientUserProvider($model);
        $this->assertEquals($model, $provider->modelClass);
    }

    #[Test]
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

    #[Test]
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