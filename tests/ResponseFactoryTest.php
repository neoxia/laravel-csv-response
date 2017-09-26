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

    public function testCsvResponseWithEmptyCollectionReturnNoContent()
    {
        $data = new Collection;

        $response = $this->responseFactory->csv($data);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals("No Content", $response->getContent());
    }

    public function testCsvResponseWithEmptyArrayReturnNoContent()
    {
        $data = [];

        $response = $this->responseFactory->csv($data);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals("No Content", $response->getContent());
    }

    public function testCsvResponseWithEmptyStringReturnNoContent()
    {
        $data = '';

        $response = $this->responseFactory->csv($data);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals("No Content", $response->getContent());
    }

    public function testCsvResponseWithCollectionOfObjectsData()
    {
        $data = new Collection([new ModelStub(['first_name' => 'John', 'last_name' => 'Doe'])]);

        $response = $this->responseFactory->csv($data);

        $this->assertCsvResponseIsValidAndEquals($response, "\"first_name\";\"last_name\"\r\n\"John\";\"Doe\"");
    }

    public function testCsvResponseWithCollectionOfArrayData()
    {
        $data = new Collection([['first_name' => 'John', 'last_name' => 'Doe']]);

        $response = $this->responseFactory->csv($data);

        $this->assertCsvResponseIsValidAndEquals($response, "\"first_name\";\"last_name\"\r\n\"John\";\"Doe\"");
    }

    public function testCsvResponseWithArrayOfObjectsData()
    {
        $data = new Collection([new ModelStub(['first_name' => 'John', 'last_name' => 'Doe'])]);

        $response = $this->responseFactory->csv($data);

        $this->assertCsvResponseIsValidAndEquals($response, "\"first_name\";\"last_name\"\r\n\"John\";\"Doe\"");
    }

    public function testCsvResponseWithSequentialArrayData()
    {
        $data = [['first_name', 'last_name'], ['John', 'Doe']];

        $response = $this->responseFactory->csv($data);

        $this->assertCsvResponseIsValidAndEquals($response, "\"first_name\";\"last_name\"\r\n\"John\";\"Doe\"");
    }

    public function testCsvResponseWithAssociativeArrayData()
    {
        $data = [['first_name' => 'John', 'last_name' => 'Doe']];

        $response = $this->responseFactory->csv($data);

        $this->assertCsvResponseIsValidAndEquals($response, "\"first_name\";\"last_name\"\r\n\"John\";\"Doe\"");
    }

    public function testCsvResponseWithStringData()
    {
        $data = "first_name;last_name\r\nJohn;Doe";

        $response = $this->responseFactory->csv($data);

        $this->assertCsvResponseIsValidAndEquals($response, "first_name;last_name\r\nJohn;Doe");
    }

    public function testCsvResponseWithCustomStatusCode()
    {
        $data = "first_name;last_name\r\nJohn;Doe";

        $response = $this->responseFactory->csv($data, 201);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCsvResponseWithCustomHeaders()
    {
        $data = "first_name;last_name\r\nJohn;Doe";

        $response = $this->responseFactory->csv($data, 200, [
            'Content-Encoding' => 'ASCII',
            'Cookie' => 'Cookie: $Version=1;',
        ]);

        $this->assertEquals('ASCII', $response->headers->get('Content-Encoding'));
        $this->assertEquals('Cookie: $Version=1;', $response->headers->get('Cookie'));
    }

    public function testCsvEncodeDataInWINDOWS1252()
    {
        $data = "first_name;last_name\r\nÉléonore;Doe";

        $response = $this->responseFactory->csv($data);

        $expectedResponse = mb_convert_encoding("first_name;last_name\r\nÉléonore;Doe", 'WINDOWS-1252');
        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testCsvCanEncodeDataInCustomEncoding()
    {
        $data = "first_name;last_name\r\nÉléonore;Doe";

        $response = $this->responseFactory->csv($data, 200, [], ['encoding' => 'UTF-8']);

        $expectedResponse = mb_convert_encoding("first_name;last_name\r\nÉléonore;Doe", 'UTF-8');
        $this->assertEquals($expectedResponse, $response->getContent());
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertEquals('UTF-8', $response->headers->get('Content-Encoding'));
    }

    public function testCsvCanFormatWithCustomDelimiter()
    {
        $data = [['first_name' => 'John', 'last_name' => 'Doe']];

        $response = $this->responseFactory->csv($data, 200, [], ['delimiter' => ',']);

        $this->assertCsvResponseIsValidAndEquals($response, "\"first_name\",\"last_name\"\r\n\"John\",\"Doe\"");
    }

    public function testCsvCanFormatWithoutQuotes()
    {
        $data = [['first_name' => 'John', 'last_name' => 'Doe']];

        $response = $this->responseFactory->csv($data, 200, [], ['quoted' => false]);

        $this->assertCsvResponseIsValidAndEquals($response, "first_name;last_name\r\nJohn;Doe");
    }

    public function testCsvCanFormatWithoutHeader()
    {
        $data = [['first_name' => 'John', 'last_name' => 'Doe']];

        $response = $this->responseFactory->csv($data, 200, [], ['includeHeader' => false]);

        $this->assertCsvResponseIsValidAndEquals($response, "\"John\";\"Doe\"");
    }

    public function testCsvEscapeQuotes()
    {
        $data = [['My comment : "This is great !"']];

        $response = $this->responseFactory->csv($data, 200, []);

        $this->assertCsvResponseIsValidAndEquals($response, "\"My comment : \"\"This is great !\"\"\"");
    }

    protected function assertCsvResponseIsValidAndEquals($response, $csvBody)
    {
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($csvBody, $response->getContent());
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
