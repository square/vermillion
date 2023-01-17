<?php

namespace Square\Vermillion\Tests\Formats\Numeric;

use Square\Vermillion\Formats\Numeric\NumericVersion;
use Square\Vermillion\Formats\Numeric\NumericNormalizer;
use Square\Vermillion\Tests\Formats\FormatTestAbstract;
use Square\Vermillion\Formats\VersionNormalizer;

class ApiVersionTest extends FormatTestAbstract
{
    protected function createNormalizer(): VersionNormalizer
    {
        return new NumericNormalizer();
    }

    protected function getApiVersionClassName(): string
    {
        return NumericVersion::class;
    }

    /**
     * @return iterable
     */
    public function dataNormalize(): iterable
    {
        yield [
            '1',
            1,
            '1',
        ];

        yield [
            '2',
            2,
            '2',
        ];

        yield [
            '10',
            10,
            '10',
        ];

        yield [
            (string) PHP_INT_MAX,
            PHP_INT_MAX,
            (string) PHP_INT_MAX,
        ];
    }

    public function dataNormalizeFails(): iterable
    {
        yield 'negative number: -1' => [
            '-1',
        ];

        yield 'float: 2.5' => [
            '2.5',
        ];

        yield 'float: 2.0' => [
            '2.0',
        ];

        yield 'semver: 0.0.1' => [
            '0.0.1',
        ];

        yield 'semver: 1.0.0' => [
            '1.0.0',
        ];
    }

}