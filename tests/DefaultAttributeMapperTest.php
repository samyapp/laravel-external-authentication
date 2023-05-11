<?php

namespace Tests;

use SamYapp\LaravelRemoteAuth\AuthConfig;
use SamYapp\LaravelRemoteAuth\DefaultAttributeMapper;

class DefaultAttributeMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @covers \SamYapp\LaravelRemoteAuth\DefaultAttributeMapper
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
            'remoteOne' => 'oneValue',
            'remoteTwo' => 'twoValue',
        ];
        $inputPrefixed = [
            'MELLON_single' => 'singleValue',
            'MELLON_remoteOne' => 'oneValue',
            'MELLON_remoteTwo' => 'twoValue',
        ];
        $config = AuthConfig::fromArray([
            'expectedAttributes' => [
                'single',
                'one' => 'remoteOne',
                'two' => 'remoteTwo',
            ]
        ]);
        $this->assertEqualsCanonicalizing($expected, $config->attributeMapper()($config, $inputNoPrefix));
        $config->attributePrefix = 'MELLON_';
        $this->assertEqualsCanonicalizing($expected, $config->attributeMapper()($config, $inputPrefixed));
    }
}