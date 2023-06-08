<?php

namespace SamYapp\LaravelExternalAuth;

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
        $this->methodNotImplemented(__METHOD__);
    }

    protected function methodNotImplemented(string $method)
    {
        throw new \Exception(sprintf('Method %s::%s is not implemented.', __CLASS__, $method));
    }
    /**
     * Get the unique identifier for the user.
     * @codeCoverageIgnore
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        $this->methodNotImplemented(__METHOD__);
    }

    /**
     * Get the password for the user.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getAuthPassword()
    {
        $this->methodNotImplemented(__METHOD__);
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getRememberToken()
    {
        $this->methodNotImplemented(__METHOD__);
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
        $this->methodNotImplemented(__METHOD__);
    }

    /**
     * Get the column name for the "remember me" token.
     * @codeCoverageIgnore
     * @return string
     */
    public function getRememberTokenName()
    {
        $this->methodNotImplemented(__METHOD__);
    }
}
