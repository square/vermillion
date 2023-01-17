<?php

declare(strict_types=1);

namespace Square\Vermillion;

use Square\Vermillion\Exceptions\NoMatchFoundException;
use Square\Vermillion\Exceptions\UnknownVersionException;
use Square\Vermillion\Exceptions\VersioningException;
use Square\Vermillion\Formats\VersionNormalizer;

class VersionedSet
{
    private VersioningManager $manager;

    /**
     * @var MaxHeap<VersionedItem>
     */
    private MaxHeap $maxHeap;

    /**
     * @param VersioningManager $manager
     */
    public function __construct(VersioningManager $manager)
    {
        $this->manager = $manager;
        $this->maxHeap = new MaxHeap();
    }

    /**
     * @param string|ApiVersion $minVersion
     * @param mixed $resource
     * @return $this
     */
    public function for(string|ApiVersion $minVersion, mixed $resource): self
    {
        $minVersion = $this->manager->getNormalizer()->normalize($minVersion);

        $item = new VersionedItem($minVersion, $resource);
        $this->maxHeap->insert($item);
        return $this;
    }

    /**
     * @param string|ApiVersion|null $version
     * @return mixed
     */
    public function resolve(string|ApiVersion|null $version = null)
    {
        $version = $version ?? $this->manager->getActive();

        if (!$version instanceof ApiVersion) {
            $version = $this->manager->getNormalizer()->normalize($version);
        }

        $this->throwIfOutsideVersionBounds($version);

        $items = clone $this->maxHeap;

        while ($items->valid()) {
            /** @var VersionedItem $item */
            $item = $items->current();
            $items->next();

            if (!$item instanceof VersionedItem) {
                continue;
            }
            if ($item->matches($version)) {
                return $item->getValue();
            }
        }

        throw new NoMatchFoundException(sprintf('Cannot find a match for version %s.', $version->toString()));
    }

    /**
     * Get list of all items that covers down to provided version (e.g. migrations)
     * @param string|ApiVersion|null $version
     * @return array<mixed>
     */
    public function resolveReversePath(string|ApiVersion|null $version = null): array
    {
        $version = $version ?? $this->manager->getActive();

        if (!$version instanceof ApiVersion) {
            $version = $this->manager->getNormalizer()->normalize($version);
        }

        $this->throwIfOutsideVersionBounds($version);

        $items = clone $this->maxHeap;
        $matches = [];
        while ($items->valid()) {
            /** @var VersionedItem $item */
            $item = $items->current();
            $items->next();

            if (!$item instanceof VersionedItem) {
                continue;
            }

            if ($item->getMinVersion()->lt($version)) {
                break;
            }

           $matches[] = $item->getValue();
        }

        return $matches;
    }

    /**
     * @param ApiVersion $version
     * @return void
     * @throws UnknownVersionException
     */
    protected function throwIfOutsideVersionBounds(ApiVersion $version): void
    {
        if ($version->gt($this->manager->max())) {
            throw new UnknownVersionException(sprintf(
                'Cannot resolve: version %s is greater than max version %s',
                $version->toString(),
                $this->manager->max()->toString(),
            ));
        }

        if ($version->lt($this->manager->min())) {
            throw new UnknownVersionException(sprintf(
                'Cannot resolve: version %s is greater than max version %s',
                $version->toString(),
                $this->manager->max()->toString(),
            ));
        }
    }
}