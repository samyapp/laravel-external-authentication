<?php

namespace SamYapp\LaravelExternalAuth;

use Illuminate\Contracts\Auth\Authenticatable;

class TransientUser implements Authenticatable
{
    /**
     * @var array - array of attribute-name => attribute-value
     */
    protected array $attributes = [];

    /**
     * @var string - Name of the attribute that uniquely identifies the user.
     */
    protected ?string $authIdentifierName = null;

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
        return $this->authIdentifierName;
    }

    /**
     * Sets the name of the attribute that uniquely identifies a user.
     * Some Laravel code expects this method to work for Authenticatable objects
     * @param string|null $name
     * @return void
     */
    public function setAuthIdentifierName(?string $name = null)
    {
        $this->authIdentifierName = $name;
    }

    protected function methodNotImplemented(string $method)
    {
        throw new \Exception(sprintf('Method %s::%s is not implemented.', __CLASS__, $method));
    }
    /**
     * Get the unique identifier for the user.
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        if ($this->getAuthIdentifierName()) {
            return $this->{$this->getAuthIdentifierName()};
        }
        throw new \InvalidArgumentException(sprintf('No authIdentifierName set in %s::%s', __CLASS__, __METHOD__));
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

    /** @codeCoverageIgnore */
    public function getAuthPasswordName()
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
