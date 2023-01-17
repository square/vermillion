<?php

return [
    /**
     * The API version format to use. Supported formats out of the box:
     *    (a) "major" e.g. 1, 2, 3, ...
     *    (b) "date" e.g. 2019-05-29, 2019-11-10, 2020-04-04, etc.
     */
    'format' => 'major',

    /**
     * The minimum API version ever available. Any routes requested with version less than this will 404.
     */
    'min' => '1',

    /**
     * The latest API version released.
     * This will be the default version set when no version was activated via the selected API scheme e.g.
     * when generating routes from controllers that aren't versioned, it will generate them with the latest version.
     */
    'latest' => '6',

    /**
     * The maximum API version ever available. Any routes requested with version greater than this will 404.
     */
    'max' => '7',

    /**
     * The API versioning scheme to use. Supported schemes out of the box:
     *    (a) "url_prefix" - Active version will be derived from URL prefix e.g. /v1, /v2019-12-20, etc.
     *    (b) "header" - Active version will be derived from HTTP header e.g. X-Api-Version: 2020-12-20
     */
    'scheme' => 'url_prefix',

    'schemes' => [
        'header' => [
            'name' => 'X-Api-Version', // If using "header" scheme, you can override the header to look for.
        ],
    ],
];
