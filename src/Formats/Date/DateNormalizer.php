<?php declare(strict_types=1);

namespace Square\Vermillion\Formats\Date;

use DateTime;
use Square\Vermillion\ApiVersion;
use Square\Vermillion\Exceptions\BadVersionFormatException;
use Square\Vermillion\Formats\Date\DateVersion;
use Square\Vermillion\Formats\VersionComparator;
use Square\Vermillion\Formats\VersionNormalizer;

/**
 * Class VersionNormalizer
 *
 * @package Square\Vermillion\Formats\Date
 */
class DateNormalizer implements VersionNormalizer
{
    /**
     * @inheritDoc
     */
    public function normalize(string|ApiVersion $version): ApiVersion
    {
        if ($version instanceof DateVersion) {
            return $version;
        }

        if (is_object($version)) {
            $this->throwIfIncompatibleType($version);
            return $version;
        }

        $this->throwIfNotString($version);

        $version = trim($version);

        $dateTime = DateTime::createFromFormat('Y-m-d', $version);
        $errors = DateTime::getLastErrors();
        if ($errors && $errors['error_count'] > 0) {
            throw new BadVersionFormatException(sprintf(
                'Error(s) encountered parsing version string %s: %s',
                $version,
                implode('; ', $errors['errors'])
            ));
        }

        assert($dateTime instanceof DateTime);
        // when php isn't given a "time", it defaults to the current system time. override this functionality
        $dateTime = $dateTime->setTime(0, 0);

        $this->throwIfInvalidDate($version, $dateTime);

        return new DateVersion($version, $dateTime->getTimestamp(), $this);
    }

    protected function throwIfIncompatibleType(mixed $version): void
    {
        if ($version instanceof ApiVersion && !$version instanceof DateVersion) {
            throw new BadVersionFormatException(sprintf(
                'Instance of %s passed, but expected instance of %s. Instance of %s given.',
                ApiVersion::class,
                DateVersion::class,
                get_class($version)
            ));
        }
    }

    protected function throwIfNotString(mixed $version): void
    {
        if (!is_string($version)) {
            $type = is_object($version) ? get_class($version) : gettype($version);
            throw new BadVersionFormatException(sprintf(
                'Expected date string in format YYYY-MM-DD or instance of %s. Got %s.',
                DateVersion::class,
                $type
            ));
        }
    }

    /**
     * PHP's DateTime is very tolerant of invalid dates i.e. it allows months and days to overflow e.g. month 13
     * will be interpreted as January. We have to make sure the date as detected is a valid date by comparing
     * it to the formatted version of the DateTime as parsed.
     *
     * @param string $version
     * @param DateTime $dateTime
     * @return void
     */
    private function throwIfInvalidDate(string $version, DateTime $dateTime)
    {
        $formatted = $dateTime->format('Y-m-d');
        if ($version !== $formatted) {
            throw new BadVersionFormatException(sprintf(
                '%s is not a valid date. Did you mean %s?',
                $version,
                $formatted,
            ));
        }
    }

    public function getComparator(): VersionComparator
    {
        return new DateComparator();
    }
}
