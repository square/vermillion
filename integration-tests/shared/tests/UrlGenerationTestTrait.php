<?php

namespace Shared\Tests;

trait UrlGenerationTestTrait
{
    /**
     * @dataProvider dataAwarenessInControllerAction
     * @param string $url
     */
    public function testAwarenessInControllerAction(string $url)
    {
        $response = $this->get($url);
        $response->assertStatus(200);
        $this->assertEquals('http://localhost' . $url, $response->getContent());
    }

    public function testGenerateSpecificVersion()
    {
        $url = route('users.list', [
            'apiVersion' => '3',
        ], false);
        $this->assertEquals('/api/v3/users', $url);
    }

    public function testDefaultToStable()
    {
        $url = route('users.list', [], false);
        $this->assertEquals('/api/v6/users', $url);
    }

    public static function dataAwarenessInControllerAction()
    {
        for ($v = 1; $v <= 7; $v++) {
            $randId = rand(1, 100);
            yield 'controller-action awareness @ v' . $v => [
                sprintf('/api/v%s/users/%d', $v, $randId),
            ];
        }
    }
}
