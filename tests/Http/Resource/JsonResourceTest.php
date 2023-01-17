<?php

namespace Square\Vermillion\Tests\Http\Resource;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Square\Vermillion\Formats\Numeric\NumericNormalizer;
use Square\Vermillion\Schemes\Header\HeaderScheme;
use Square\Vermillion\VersioningManager;

class JsonResourceTest extends TestCase
{
    private VersioningManager $manager;

    private Person $person;

    public function setUp(): void
    {
        $this->manager = new VersioningManager(
            $normalizer = new NumericNormalizer(),
            new HeaderScheme(),
            $normalizer->normalize('10'),
            $normalizer->normalize('1'),
            $normalizer->normalize('10'),
        );
        $this->person = new Person();
        $this->person->name = 'Robert';
        $this->person->nickName = 'Bob';
        $this->person->age = 30;
        Container::getInstance()->instance(VersioningManager::class, $this->manager);
        Container::getInstance()->instance('request', Request::capture());
    }

    /**
     * @throws \JsonException
     */
    public function testLatestVersion()
    {
        $resource = new PersonResource($this->person);
        $data = json_decode(json_encode($resource), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals(
            [
                'display_name' => 'Robert',
                'nickname' => 'Bob',
                'age' => 30,
            ],
            $data,
        );
    }

    /**
     * @throws \JsonException
     * @dataProvider dataVersions
     */
    public function testVersions($version, array $expectedData)
    {
        $this->manager->setActive($version);
        $resource = new PersonResource($this->person);
        $data = json_decode(json_encode($resource), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals(
            $expectedData,
            $data,
        );
    }

    /**
     * @throws \JsonException
     */
    public function dataVersions(): iterable
    {
        yield 'v8' => [
            '8',
            [
                'display_name' => 'Robert',
                'nickname' => 'Bob',
                'age' => 30,
            ],
        ];
        yield 'v7' => [
            '7',
            [
                'display_name' => 'Robert',
                'age' => 30,
            ],
        ];

        yield 'v6' => [
            '6',
            [
                'display_name' => 'Robert',
                'age' => 30,
            ],
        ];

        yield 'v5' => [
            '5',
            [
                'display_name' => 'Robert',
                'age' => 30,
                'hobbies' => [],
            ]
        ];

        yield 'v4' => [
            '4',
            [
                'display_name' => 'Robert',
                'age' => 30,
                'hobbies' => [],
            ]
        ];

        yield 'v3' => [
            '3',
            [
                'display_name' => 'Robert',
                'age' => 30,
                'hobbies' => [],
            ]
        ];

        yield 'v2' => [
            '2',
            [
                'name' => 'Robert',
                'age' => 30,
                'hobbies' => [],
            ]
        ];

        yield 'v1' => [
            '1',
            [
                'name' => 'Robert',
                'age' => 30,
                'hobbies' => [],
                'always_true' => true,
            ],
        ];


    }
}