<?php

namespace SamYapp\LaravelExternalAuth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SamYapp\LaravelExternalAuth\ExternalAuthGuard;

/**
 * Event triggered when ExternalAuthGuard attempts authentication and all required attributes are present
 * but the UserProvider cannot find a matching user.
 *
 * Listen to this event to enable creating local user accounts the first time they authenticate or to
 * log authentication attempts where user attributes are present but do not match.
 */
class UnknownUserAuthenticating
{
    use Dispatchable;

    public function __construct(
        /** @var - User attributes mapped from the request input (e.g. username, email, name, etc) by the guard */
        public array $attributes,
        /** @var - The ExternalAuthGuard instance (whose $input and $config properties can be accessed if required) */
        public ExternalAuthGuard $guard,
    ){}
}