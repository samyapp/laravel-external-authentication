<?php

namespace Tests;

use Illuminate\Foundation\Auth\User;
use Mockery\MockInterface;
use SamYapp\LaravelRemoteAuth\AuthConfig;
use SamYapp\LaravelRemoteAuth\DefaultUserCreator;
use SamYapp\LaravelRemoteAuth\DefaultUserSyncer;
use SamYapp\LaravelRemoteAuth\TransientUser;
use Mockery;

/**
 * @covers \SamYapp\LaravelRemoteAuth\DefaultUserSyncer
 */
class DefaultUserSyncerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function __invokeCallsSaveMethodOnAutenticatableIfItExists()
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('save');
        (new DefaultUserSyncer())($user, [], AuthConfig::fromArray([]));
    }

    /**
     * @test
     */
    public function __invokeDoesNotCallSaveMethodOnAuthenticatableIfItDoesNotExist()
    {
        // TransientUser does not have a save() method
        $user = new TransientUser;
        $this->assertFalse(method_exists($user, 'save'));
        (new DefaultUserSyncer())($user, [], AuthConfig::fromArray([]));
    }
}