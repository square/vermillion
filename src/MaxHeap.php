<?php

namespace Square\Vermillion;

use InvalidArgumentException;
use SplMaxHeap;

/**
 * @extends SplMaxHeap<VersionedItem>
 */
class MaxHeap extends SplMaxHeap
{
    /**
     * @param VersionedItem $value1
     * @param VersionedItem $value2
     * @return int
     */
    public function compare($value1, $value2): int
    {
        return $value1->getMinVersion()->compare($value2->getMinVersion());
    }

    /**
     * @param $value
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function insert($value)
    {
        if (!$value instanceof VersionedItem) {
            $type = get_debug_type($value);
            throw new InvalidArgumentException(sprintf(
                'Expected an instance of %s. Got %s.',
                VersionedItem::class,
                get_debug_type($type),
            ));
        }
        return parent::insert($value);
    }
}