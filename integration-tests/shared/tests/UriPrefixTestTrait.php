<?php

namespace Shared\Tests;

trait UriPrefixTestTrait
{
    /**
     * @dataProvider dataUriVersions
     * @param string $method
     * @param string $uri
     * @param int $status
     * @param string|null $content
     */
    public function testUriVersions(string $method, string $uri, int $status, ?string $content = null)
    {
        $response = $this->call($method, $uri);
        $response->assertStatus($status);
        if (!$content === null) {
            $this->assertEquals($content, $response->getContent());
        }
    }

    public function dataUriVersions()
    {
        // v1
        yield 'GET /users @ v1' => [
            'GET',
            '/api/v1/users',
            200,
            'Shared\Controllers\UsersController::listUsers',
        ];
        yield 'HEAD /users @ v1' => [
            'HEAD',
            '/api/v1/users',
            200,
        ];
        yield 'POST /users @ v1' => [
            'POST',
            '/api/v1/users',
            405,
        ];
        yield 'POST /members @v1' => [
            'POST',
            '/api/v1/members',
            405,
        ];

        // v2
        yield 'GET /users @ v2' => [
            'GET',
            '/api/v2/users',
            200,
            'Shared\Controllers\UsersController::listUsers',
        ];
        yield 'HEAD /users @ v2' => [
            'HEAD',
            '/api/v2/users',
            200,
        ];
        yield 'POST /users @ v2' => [
            'POST',
            '/api/v2/users',
            405,
        ];
        yield 'POST /members @v2' => [
            'POST',
            '/api/v2/members',
            405,
        ];

        // v3
        yield 'GET /users @ v3' => [
            'GET',
            '/api/v3/users',
            200,
            'Shared\Controllers\UsersController::listUsersV3',
        ];
        yield 'HEAD /users @ v3' => [
            'HEAD',
            '/api/v3/users',
            200,
        ];
        yield 'POST /users @ v3' => [
            'POST',
            '/api/v3/users',
            405,
        ];
        yield 'POST /members @ v3' => [
            'POST',
            '/api/v3/members',
            201,
            'Shared\Controllers\UsersController::create',
        ];

        // v4
        yield 'GET /users @ v4' => [
            'GET',
            '/api/v4/users',
            200,
            'Shared\Controllers\UsersController::listUsersV4',
        ];
        yield 'HEAD /users @ v4' => [
            'HEAD',
            '/api/v4/users',
            200,
        ];
        yield 'POST /users @ v4' => [
            'POST',
            '/api/v4/users',
            405,
        ];
        yield 'POST /members @ v4' => [
            'POST',
            '/api/v4/members',
            201,
            'Shared\Controllers\UsersController::create',
        ];

        // v5
        yield 'GET /users @ v5' => [
            'GET',
            '/api/v5/users',
            200,
            'Shared\Controllers\UsersController::listUsersV4',
        ];
        yield 'HEAD /users @ v5' => [
            'HEAD',
            '/api/v5/users',
            200,
        ];
        yield 'POST /users @ v5' => [
            'POST',
            '/api/v5/users',
            405,
        ];
        yield 'POST /members @ v5' => [
            'POST',
            '/api/v5/members',
            201,
            'Shared\Controllers\UsersController::create',
        ];

        // v6
        yield 'GET /users @ v6' => [
            'GET',
            '/api/v6/users',
            200,
            'Shared\Controllers\UsersController::listUsersV4',
        ];
        yield 'HEAD /users @ v6' => [
            'HEAD',
            '/api/v6/users',
            200,
        ];
        yield 'POST /users @ v6' => [
            'POST',
            '/api/v6/users',
            405,
        ];
        yield 'POST /members @ v6' => [
            'POST',
            '/api/v6/members',
            201,
            'Shared\Controllers\UsersController::create',
        ];

        // v7
        yield 'GET /users @ v7' => [
            'GET',
            '/api/v7/users',
            404,
        ];
        yield 'HEAD /users @ v7' => [
            'HEAD',
            '/api/v7/users',
            404,
        ];
        yield 'POST /users @ v7' => [
            'POST',
            '/api/v7/users',
            405,
        ];
        yield 'POST /members @ v7' => [
            'POST',
            '/api/v7/members',
            201,
            'Shared\Controllers\UsersController::create',
        ];

        // v8 - Unsupported version
        yield 'GET /users @ v8' => [
            'GET',
            '/api/v8/users',
            404,
        ];
        yield 'HEAD /users @ v8' => [
            'HEAD',
            '/api/v8/users',
            404,
        ];
        yield 'POST /users @ v8' => [
            'POST',
            '/api/v8/users',
            405,
        ];
        yield 'POST /members @ v8' => [
            'POST',
            '/api/v8/members',
            404,
        ];
    }

}
