<?php

use Neoxia\Routing\ResponseFactory;
use Illuminate\Support\Collection;
use Mockery as m;

class ResponseFactoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $redirector = m::mock('Illuminate\Routing\Redirector');

        $this->responseFactory = new ResponseFactory($viewFactory, $redirector);
    }

    public function testCsvResponseWithEmptyDataReturnNoContent()
    {
        $data = new Collection;

        $response = $this->responseFactory->csv($data);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals("No Content", $response->getContent());
    }

    public function testCsvResponseWithBasicData()
    {
        $data = new Collection([new ModelStub(['first_name' => 'John', 'last_name' => 'Doe'])]);

        $response = $this->responseFactory->csv($data);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("first_name;last_name\r\nJohn;Doe", $response->getContent());
        $this->assertEquals('text/csv; charset=WINDOWS-1252', $response->headers->get('Content-Type'));
        $this->assertEquals('WINDOWS-1252', $response->headers->get('Content-Encoding'));
        $this->assertEquals('binary', $response->headers->get('Content-Transfer-Encoding'));
        $this->assertEquals('File Transfer', $response->headers->get('Content-Description'));
    }
}

class ModelStub
{
    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function csvSerialize()
    {
        return $this->attributes;
    }
}
