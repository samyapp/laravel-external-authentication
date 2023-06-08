<?php

namespace SamYapp\LaravelExternalAuth;

/**
 * Stores configured authentication attribute details
 */
final class AuthAttribute
{
	public function __construct(
		/** @var string - The App's name for the attribute  */
		public readonly string  $name,
		/** @var null|string - The name of the attribute provided by the external auth source (or null if == $name)  */
		public readonly ?string $externalName = null,
		public readonly bool    $required = false
	){}
}