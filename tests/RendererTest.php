<?php

namespace Tests;

use Fig\Http\Message\StatusCodeInterface;
use Francerz\Http\HttpFactory;
use Francerz\Render\Renderer;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    private $httpFactory;
    private $renderer;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $httpFactory = new HttpFactory();
        $renderer = new Renderer($httpFactory, $httpFactory);
        $renderer->setViewsPath(dirname(__FILE__, 2) . '/testViews');

        $this->httpFactory = $httpFactory;
        $this->renderer = $renderer;
    }

    /**
     * @runInSeparateProcess
     */
    public function testRender()
    {
        $response = $this->renderer->render('first');

        $expected = $this->httpFactory
            ->createResponse(StatusCodeInterface::STATUS_CREATED)
            ->withHeader('Content-type', 'text/html;charset=UTF-8')
            ->withHeader('X-Test-Header', 'MyTest')
            ->withBody($response->getBody());

        $htmlPath = dirname(__FILE__, 2) . '/testViews/html/content.html';
        $htmlContent = file_get_contents($htmlPath);

        $this->assertEquals($expected, $response);
        $this->assertEquals($htmlContent, (string)$response->getBody());
    }

    public function testRenderJson()
    {
        $response = $this->renderer->renderJson();
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('[]', (string)$response->getBody());

        $response = $this->renderer->renderJson(null);
        $this->assertEquals('null', (string)$response->getBody());

        $response = $this->renderer->renderJson(1);
        $this->assertEquals('1', (string)$response->getBody());

        $response = $this->renderer->renderJson("1");
        $this->assertEquals('"1"', (string)$response->getBody());

        $response = $this->renderer->renderJson(['a' => 1]);
        $this->assertEquals('{"a":1}', (string)$response->getBody());
    }

    public function testRenderHTML()
    {
        $response = $this->renderer->render('content');

        $this->assertTrue(true);
        $expected = "<main><p>Inner</p>\n</main>\n";
        $actual = (string)$response->getBody();
        $this->assertEquals($expected, $actual);
    }
}
