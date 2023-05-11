<?php

namespace SamYapp\LaravelRemoteAuth;

/**
 * Stores configured authentication attribute details
 */
final class AuthAttribute
{
	public function __construct(
		/** @var string - The App's name for the attribute  */
		public readonly string $name,
		/** @var null|string - The name of the attribute provided by the remote auth source (or null if == $name)  */
		public readonly ?string $remoteName = null,
		public readonly bool $required = false
	){}
}