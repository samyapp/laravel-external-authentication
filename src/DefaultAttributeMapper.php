<?php

namespace SamYapp\LaravelRemoteAuth;

/**
 * Default callable to map remote server, request or env variables to user attributes
 */
class DefaultAttributeMapper
{
    /**
     * Maps keys and values from $input to an array with the keys renamed as required for user attributes
     * If remoteName contains a '*' wildcard, the matching values are mapped to an array.
     * e.g. remoteName = 'department*'
     * and input contains: ['department_1' => 'sales', 'department_2' => 'admin'],
     * then output is: ['department' => ['sales','admin']]
     * @param AuthConfig $config
     * @param array $input
     * @return array
     */
    public function __invoke(AuthConfig $config, array $input)
    {
        $attributes = [];
        foreach ($config->expectedAttributes as $attribute) {
            $key = $config->attributePrefix . $attribute->remoteName;
            // if an match exists for exact key or lower or uppercase take that value
            foreach ([$key, strtolower($key), strtoupper($key)] as $inputKey) {
                if (array_key_exists($inputKey, $input)) {
                    $attributes[$attribute->name] = $input[$inputKey];
                }
            }
            // if there wasn't an exact match and the key has any regular expression special characters,
            // see if we can match an array of attributes
            if (!array_key_exists($attribute->name, $attributes) && preg_match('/[^a-z0-9_-]/i',$key)) {
                $matches = [];
                foreach ($input as $k => $value) {
                    if (preg_match(sprintf('~^%s$~i', $key),$k)) {
                        $matches[] = $value;
                    }
                }
                if ($matches) {
                    $attributes[$attribute->name] = $matches;
                }
            }
       }
        return $attributes;
    }
}