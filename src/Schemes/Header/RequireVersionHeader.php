<?php

namespace Square\Vermillion\Schemes\Header;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Square\Vermillion\Exceptions\VersionMissingException;
use Square\Vermillion\VersioningManager;

class RequireVersionHeader
{
    /**
     * @param Request $request
     * @param callable $next
     * @return mixed|void
     */
    public function handle(Request $request, $next)
    {
        $manager = app(VersioningManager::class);
        $scheme = $manager->getScheme();

        if (!$scheme instanceof HeaderScheme) {
            return $next($request);
        }

        $headerName = $scheme->getHeaderName();

        if (!$request->headers->has($headerName)) {
            throw new VersionMissingException(sprintf('API versioning header "%s" is missing.', $headerName));
        }

        $value = $request->headers->get($headerName);

        assert(is_string($value));
        // This will throw the appropriate exception if header value is invalid.
        $manager->getNormalizer()->normalize($value);

        return $next($request);
    }
}