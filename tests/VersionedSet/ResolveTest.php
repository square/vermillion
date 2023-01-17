<?php

namespace Square\Vermillion\Tests\VersionedSet;

use PHPUnit\Framework\TestCase;
use Square\Vermillion\ApiVersion;
use Square\Vermillion\Exceptions\UnknownVersionException;
use Square\Vermillion\Formats\Numeric\NumericNormalizer;
use Square\Vermillion\Schemes\Header\HeaderScheme;
use Square\Vermillion\VersionedSet;
use Square\Vermillion\Schemes\VersioningScheme;
use Square\Vermillion\VersioningManager;

class ResolveTest extends TestCase
{
    /**
     * @param VersionedSet $set
     * @param $version
     * @param $expectedValue
     * @return void
     * @dataProvider dataResolve
     */
    public function testResolve(VersionedSet $set, $version, $expectedValue)
    {
        $value = $set->resolve($version);
        $this->assertEquals($expectedValue, $value);
    }

    /**
     * @param VersionedSet $set
     * @param $version
     * @return void
     * @dataProvider dataResolveFails
     */
    public function testResolveFails(VersionedSet $set, $version)
    {
        $this->expectException(UnknownVersionException::class);
        $set->resolve($version);
    }

    /**
     * @param VersionedSet $set
     * @param $version
     * @param array $expectedValues
     * @return void
     * @dataProvider dataResolveRange
     */
    public function testResolveRange(VersionedSet $set, $version, array $expectedValues)
    {
        $this->assertEquals($expectedValues, $set->resolveReversePath($version));
    }

    /**
     * @return iterable
     */
    public function dataResolve(): iterable
    {
        $normalizer = new NumericNormalizer();
        $scheme = new HeaderScheme();
        $currentVersion = $normalizer->normalize('7');
        $minVersion = $normalizer->normalize('3');
        $maxVersion = $normalizer->normalize('10');
        $manager = new VersioningManager(
            $normalizer,
            $scheme,
            $currentVersion,
            $minVersion,
            $maxVersion,
        );

        $set = new VersionedSet($manager);
        $set->for('3', 'Three')
            ->for('5', 'Five')
            ->for('8', 'Eight')
            ->for('10', 'Ten');

        $generator = static function (?string $version, string $expectedValue) use ($normalizer, $set) {
            yield sprintf('"%s" resolves "%s"', $version ?? 'NULL', $expectedValue) => [
                $set,
                $version,
                $expectedValue,
            ];
            if ($version === null) {
                return;
            }
            yield sprintf('ApiVersion("%s") resolves "%s"', $version, $expectedValue) => [
                $set,
                $normalizer->normalize($version),
                $expectedValue,
            ];
        };

        yield from $generator('3', 'Three');
        yield from $generator('4', 'Three');
        yield from $generator('5', 'Five');
        yield from $generator('6', 'Five');
        yield from $generator('7', 'Five');
        yield from $generator(null, 'Five');
        yield from $generator('8', 'Eight');
        yield from $generator('9', 'Eight');
        yield from $generator('10', 'Ten');
    }

    /**
     * @return iterable
     */
    public function dataResolveFails(): iterable
    {
        $normalizer = new NumericNormalizer();
        $scheme = new HeaderScheme();
        $currentVersion = $normalizer->normalize('7');
        $minVersion = $normalizer->normalize('3');
        $maxVersion = $normalizer->normalize('10');
        $manager = new VersioningManager(
            $normalizer,
            $scheme,
            $currentVersion,
            $minVersion,
            $maxVersion,
        );

        $set = new VersionedSet($manager);
        $set->for('3', 'Three')
            ->for('5', 'Five')
            ->for('8', 'Eight')
            ->for('10', 'Ten');

        $generator = static function (?string $version, string $reason) use ($normalizer, $set) {
            yield sprintf('"%s" fails - %s', $version ?? 'NULL', $reason) => [
                $set,
                $version,
            ];
            yield sprintf('ApiVersion("%s") fails - %s', $version, $reason) => [
                $set,
                $normalizer->normalize($version),
            ];
        };

        yield from $generator('2', 'below min version');
        yield from $generator('1', 'below min version');
        yield from $generator('11', 'above max version');
        yield from $generator('12', 'above max version');
        yield from $generator('13', 'above max version');
    }

    /**
     * @return iterable
     */
    public function dataResolveRange(): iterable
    {
        $normalizer = new NumericNormalizer();
        $scheme = new HeaderScheme();
        $currentVersion = $normalizer->normalize('7');
        $minVersion = $normalizer->normalize('3');
        $maxVersion = $normalizer->normalize('12');
        $manager = new VersioningManager(
            $normalizer,
            $scheme,
            $currentVersion,
            $minVersion,
            $maxVersion,
        );

        $set = new VersionedSet($manager);
        $set->for('3', 'Three')
            ->for('5', 'Five')
            ->for('8', 'Eight')
            ->for('10', 'Ten');

        $generator = static function (?string $version, array $expectedValues) use ($normalizer, $set) {
            yield sprintf('"%s" resolves "%s"', $version ?? 'NULL', json_encode($expectedValues, JSON_THROW_ON_ERROR)) => [
                $set,
                $version,
                $expectedValues,
            ];
            if ($version === null) {
                return;
            }
            yield sprintf('ApiVersion("%s") resolves "%s"', $version, json_encode($expectedValues, JSON_THROW_ON_ERROR)) => [
                $set,
                $normalizer->normalize($version),
                $expectedValues,
            ];
        };

        yield from $generator('3', ['Ten', 'Eight', 'Five', 'Three']);
        yield from $generator('4', ['Ten', 'Eight', 'Five']);
        yield from $generator('5', ['Ten', 'Eight', 'Five']);
        yield from $generator('6', ['Ten', 'Eight']);
        yield from $generator('7', ['Ten', 'Eight']);
        yield from $generator(null, ['Ten', 'Eight']);
        yield from $generator('8', ['Ten', 'Eight']);
        yield from $generator('9', ['Ten']);
        yield from $generator('10', ['Ten']);
    }
}