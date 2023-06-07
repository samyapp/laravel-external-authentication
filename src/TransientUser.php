<?php

namespace SamYapp\LaravelRemoteAuth;

use Illuminate\Contracts\Auth\Authenticatable;

class TransientUser implements Authenticatable
{
    protected array $attributes = [];

    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getAuthIdentifierName()
    {

    }

    /**
     * Get the unique identifier for the user.
     * @codeCoverageIgnore
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {

    }

    /**
     * Get the password for the user.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getAuthPassword()
    {

    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getRememberToken()
    {

    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     * @return void
     * @codeCoverageIgnore
     */
    public function setRememberToken($value)
    {
        
    }

    /**
     * Get the column name for the "remember me" token.
     * @codeCoverageIgnore
     * @return string
     */
    public function getRememberTokenName()
    {
        
    }
}