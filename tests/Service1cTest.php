<?php

namespace novatorgroup\service1c\tests;

use novatorgroup\service1c\HttpService;

class Service1cTest extends \PHPUnit\Framework\TestCase
{
    private $params;

    public function setUp()
    {
        $this->params = json_decode(file_get_contents(__DIR__ . '\params.json'), true);
    }

    public function testIncorrectHost()
    {
        $service = new HttpService([
            'host' => 'http://error.host',
            'base' => 'base'
        ]);

        $response = $service->get('command', ['aaa']);
        $this->assertEmpty($response);
    }

    public function testIncorrectParam()
    {
        $service = new HttpService($this->params);

        $response = $service->get('bonus', ['test_incorrect']);
        $this->assertEmpty($response);
    }

    public function testCorrectRequest()
    {
        $service = new HttpService($this->params);

        $code = 'Ц00012408';

        $response = $service->get('bonus', [$code]);
        $this->assertNotEmpty($response);
        $this->assertNotEquals(mb_strpos($response, $code), false);
    }
}