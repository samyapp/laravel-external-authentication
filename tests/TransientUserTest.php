<?php

namespace Tests;

use Illuminate\Foundation\Auth\User;
use SamYapp\LaravelExternalAuth\TransientUser;
use SamYapp\LaravelExternalAuth\TransientUserProvider;

/**
 * @covers SamYapp\LaravelExternalAuth\TransientUser
 */
class TransientUserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function getAndSetGetAndSetAttributes()
    {
        $attrs = ['foo' => 'bar', 'roles' => ['bread', 'sausage']];
        $user = new TransientUser();
        foreach ($attrs as $name => $value) {
            $user->$name = $value;
        }
        foreach ($attrs as $name => $value) {
            $this->assertEquals($value, $user->$name);
        }
    }
}