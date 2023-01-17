<?php

namespace Square\Vermillion\Tests\Formats;

use PHPUnit\Framework\TestCase;
use Square\Vermillion\Exceptions\BadVersionFormatException;
use Square\Vermillion\Formats\VersionNormalizer;
use Square\Vermillion\ApiVersion as ApiVersionAbstract;

abstract class FormatTestAbstract extends TestCase
{
    /**
     * @var VersionNormalizer
     */
    protected VersionNormalizer $normalizer;

    public function setUp(): void
    {
        $this->normalizer = $this->createNormalizer();
    }

    /**
     * @return void
     * @dataProvider dataNormalize
     */
    public function testNormalize($toNormalize, $intValue, $stringValue)
    {
        $version = $this->normalizer->normalize($toNormalize);
        $this->assertInstanceOf(ApiVersionAbstract::class, $version);
        $this->assertInstanceOf($this->getApiVersionClassName(), $version);
        $this->assertEquals($intValue, $version->toInt());
        $this->assertEquals($stringValue, $version->toString());
        $this->assertTrue($version->eq($toNormalize));
    }

    /**
     * @param $toNormalize
     * @param string|null $exceptionClass
     * @param string|null $exceptionMessage
     * @return void
     * @dataProvider dataNormalizeFails
     */
    public function testNormalizeFails($toNormalize, string $exceptionClass = null, string $exceptionMessage = null)
    {
        $this->expectException($exceptionClass ?? BadVersionFormatException::class);
        if ($exceptionMessage !== null) {
            $this->expectExceptionMessage($exceptionMessage);
        }
        $this->normalizer->normalize($toNormalize);
    }


    abstract protected function createNormalizer(): VersionNormalizer;

    /**
     * @return class-string
     */
    abstract protected function getApiVersionClassName(): string;

    /**
     * @return iterable
     */
    abstract public  function dataNormalize(): iterable;

    /**
     * @return iterable
     */
    abstract public function dataNormalizeFails(): iterable;
}