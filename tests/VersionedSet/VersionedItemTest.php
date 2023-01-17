<?php

namespace Square\Vermillion\Tests\VersionedSet;

use PHPUnit\Framework\TestCase;
use Square\Vermillion\Formats\Numeric\NumericVersion;
use Square\Vermillion\Formats\Numeric\NumericNormalizer;
use Square\Vermillion\VersionedItem;

class VersionedItemTest extends TestCase
{
    private NumericNormalizer $normalizer;

    public function setUp(): void
    {
        $this->normalizer = new NumericNormalizer();
    }

    /**
     * @return void
     */
    public function testValue()
    {
        $obj = new \stdClass;
        $item = new VersionedItem($this->normalizer->normalize('1'), $obj);
        $this->assertSame($obj, $item->getValue());
    }

    /**
     * @return void
     */
    public function testMinVersion()
    {
        $obj = new \stdClass;
        $item = new VersionedItem($version = $this->normalizer->normalize('1'), $obj);
        $this->assertSame($version, $item->getMinVersion());
    }
}