<?php

namespace tests\Clearcode\EHLibrarySandbox\Slim;

class TestTest extends WebTestCase
{
    public function testItWorks()
    {
        $this->request('/foo');

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $this->response);
        $this->assertEquals('Hello', (string) $this->response->getBody());
    }
}