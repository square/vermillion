<?php

namespace Shared\Controllers;

use Illuminate\Http\Request;
use Square\Vermillion\ApiVersion;
use Square\Vermillion\Formats\Date\DateNormalizer;
use Square\Vermillion\Formats\Date\DateVersion;
use Square\Vermillion\Formats\Numeric\NumericVersion;

class UsersController extends Controller
{
    /**
     * @param Request $request
     * @param NumericVersion $v
     * @return string
     */
    public function listUsers(Request $request, ApiVersion $v): string
    {
        return __METHOD__;
    }

    public function listUsersV3(Request $request): string
    {
        return __METHOD__;
    }

    public function listUsersV4(Request $request): string
    {
        return __METHOD__;
    }

    /**
     * @param Request $request
     * @param string $id
     * @return string
     */
    public function show(Request $request, mixed $id): string
    {
        // Used in assertions checking that current API version is inferred during route generation,
        // and positional arguments are current even without $apiVersion in the action's arg list.
        return route('users.show', [
            'id' => $id,
        ]);
    }
}
