<?php

namespace SamYapp\LaravelExternalAuth\Events;

use Illuminate\Foundation\Bus\Dispatchable;
use SamYapp\LaravelExternalAuth\AuthAttribute;
use SamYapp\LaravelExternalAuth\ExternalAuthGuard;

class IncompleteAuthenticationAttributes
{
    use Dispatchable;
    /**
     * @param AuthAttribute[] $missingAttributes
     * @param string[] $attributes
     * @param ExternalAuthGuard $param
     */
    public function __construct(
        /** @var AuthAttribute[] $missingAttributes - Required AuthAttributes missing from the request */
        public array $missingAttributes,
        /** @var string[] $attributes - Attribute values keyed by name that were present in the request */
        public array $attributes,
        /** @var ExternalAuthGuard $guard - The guard that dispatched the event */
        public ExternalAuthGuard $guard)
    {
    }
}