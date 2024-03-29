<?php

namespace Tests;

use SamYapp\LaravelExternalAuth\AuthConfig;
use SamYapp\LaravelExternalAuth\DefaultAttributeMapper;

/**
 * @covers \SamYapp\LaravelExternalAuth\DefaultAttributeMapper
 * @covers \SamYapp\LaravelExternalAuth\AuthConfig
 */
class DefaultAttributeMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function __invokeMatchesSingleValueAttributesWithAndWithoutPrefix()
    {
        $expected = [
            'single' => 'singleValue',
            'one' => 'oneValue',
            'two' => 'twoValue',
        ];
        $inputNoPrefix = [
            'single' => 'singleValue',
            'externalOne' => 'oneValue',
            'externalTwo' => 'twoValue',
        ];
        $inputPrefixed = [
            'MELLON_single' => 'singleValue',
            'MELLON_externalOne' => 'oneValue',
            'MELLON_externalTwo' => 'twoValue',
        ];
        $config = AuthConfig::fromArray([
            'attributeMap' => [
                'single',
                'one' => 'externalOne',
                'two' => 'externalTwo',
            ]
        ]);
        $this->assertEqualsCanonicalizing($expected, $config->attributeMapper()($config, $inputNoPrefix));
        $config->attributePrefix = 'MELLON_';
        $this->assertEqualsCanonicalizing($expected, $config->attributeMapper()($config, $inputPrefixed));
    }

    /**
     * @test
     */
    public function __invokeMatchesUsingRegexIntoArray()
    {
        // the configured attributes and mappings expected
        $config = AuthConfig::fromArray([
            'attributeMap' => [
                'single',
                'firstArray' => '.*?_first_.*', // match a regex
                'secondArray' => 'MELLON_second.*', // match a different regex
                'missingArray' => ['required' => false, 'MELLON_third_.*'], // and a third regex,but with not required
            ]
        ]);
        // the input
        $input = [
            'single' => 'singleValue',
            'MELLON_first_0' => '1.1',
            'MELLON_first_1' => '1.2',
            'MELLON_second_0' => '2.1',
            'MELLON_second_1' => '2.2',
        ];
        // the results we expect to get
        $expected = [
            'single' => 'singleValue',
            'firstArray' => ['1.1', '1.2'],
            'secondArray' => ['2.1', '2.2'],
        ];
        $attributes = $config->attributeMapper()($config, $input);
        $this->assertEqualsCanonicalizing($expected, $attributes);

        // attribute "missingArray" should not be defined as it is not present in the input
        $this->assertArrayNotHasKey('missingArray', $attributes);
    }
}