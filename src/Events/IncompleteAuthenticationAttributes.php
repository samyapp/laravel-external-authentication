<?php

namespace SamYapp\LaravelRemoteAuth\Events;

use Illuminate\Foundation\Bus\Dispatchable;
use SamYapp\LaravelRemoteAuth\AuthAttribute;
use SamYapp\LaravelRemoteAuth\RemoteAuthGuard;

class IncompleteAuthenticationAttributes
{
    use Dispatchable;
    /**
     * @param AuthAttribute[] $missingAttributes
     * @param string[] $attributes
     * @param RemoteAuthGuard $param
     */
    public function __construct(
        /** @var AuthAttribute[] $missingAttributes - Required AuthAttributes missing from the request */
        public array $missingAttributes,
        /** @var string[] $attributes - Attribute values keyed by name that were present in the request */
        public array $attributes,
        /** @var RemoteAuthGuard $guard - The guard that dispatched the event */
        public RemoteAuthGuard $guard)
    {
    }
}