<?php

namespace novatorgroup\service1c\tests;

use novatorgroup\service1c\HttpService;

class Service1cTest extends \PHPUnit\Framework\TestCase
{
    private array $serviceParams = [];

    public function setUp(): void
    {
        $this->serviceParams = json_decode(file_get_contents(__DIR__ . '\params.json'), true);
    }

    public function testIncorrectHost()
    {
        $service = new HttpService([
            'host' => 'http:/incorrect.host/',
            'base' => 'base'
        ]);

        $response = $service->get('command', ['aaa']);
        $this->assertFalse($response->isOk());
        $this->assertEmpty($response->result);
    }

    public function testIncorrectParam()
    {
        $service = new HttpService($this->serviceParams);

        $response = $service->get('bonus', ['test_incorrect']);

        $this->assertEmpty($response->error);
        $this->assertNotEmpty($response->result);
        $this->assertEquals(404, $response->code);
    }

    public function testCorrectRequest()
    {
        $service = new HttpService($this->serviceParams);

        $code = 'Ц00012408';
        $response = $service->get('bonus', [$code]);

        $this->assertTrue($response->isOk());
        $this->assertEmpty($response->error);
        $this->assertEquals(200, $response->code);
        $this->assertNotEquals(false, mb_strpos($response->result, $code));
        $this->assertNotEmpty($response->headers);
        $this->assertNotEmpty($response->getHeader('Content-Length'));
        $this->assertNull($response->getHeader('NOT-EXIST'));
    }

    public function testPostRequestIncorrectParam()
    {
        $service = new HttpService($this->serviceParams);
        $requestParams = json_decode(file_get_contents(__DIR__ . '\post_incorrect.json'), true);
        $response = $service->post('discounts', $requestParams);

        $this->assertFalse($response->isOk());
        $this->assertStringContainsString('Поле объекта не обнаружено', $response->result);

        $xml = @simplexml_load_string($response->result);
        $this->assertEmpty($xml);
    }

    public function testPostRequestCorrectParams()
    {
        $service = new HttpService($this->serviceParams);
        $requestParams = json_decode(file_get_contents(__DIR__ . '\post_correct.json'), true);
        $response = $service->post('discounts', $requestParams);

        $this->assertTrue($response->isOk());

        $xml = @simplexml_load_string($response->result);
        $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    }
}