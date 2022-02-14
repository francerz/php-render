<?php

namespace Francerz\Render;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

class Renderer extends SuperContainer
{
    /** @var string */
    private $viewsPath;
    /** @var ResponseFactoryInterface */
    private $responseFactory;
    /** @var StreamFactoryInterface */
    private $streamFactory;

    /* SERVER RESPONSE BACKUP ATTRIBUTES */
    /** @var int */
    private $backCode;
    /** @var string[] */
    private $backHeaders;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
    }

    public function setViewsPath(?string $viewsPath = null)
    {
        $this->viewsPath = $viewsPath;
    }
    public function setResponseFactory(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function setStreamFactory(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    private static function getCurrentHeaders()
    {
        if (PHP_SAPI !== 'cli') {
            return \headers_list();
        }

        if (function_exists('xdebug_get_headers')) {
            return \xdebug_get_headers();
        }

        return [];
    }

    private static function getRenderHeaders()
    {
        $headers = [];
        $list = static::getCurrentHeaders();
        foreach ($list as $header) {
            [$hname, $hcontent] = explode(':', $header, 2);
            $headers[$hname] = trim($hcontent);
        }
        return $headers;
    }

    private function backServerState()
    {
        $this->backHeaders = static::getCurrentHeaders();
        $this->backCode = http_response_code();
    }

    private function restoreServerState()
    {
        if (headers_sent()) {
            return;
        }

        http_response_code($this->backCode);
        header_remove();
        foreach ($this->backHeaders as $header) {
            header($header);
        }
    }

    public function render(string $view, array $data = [])
    {
        // Checks if $view is valid
        static::setSharedViewsPath($this->viewsPath);
        $view = $this->getViewPath($view);

        // Backs status and headers on server.
        $this->backServerState();

        // Start output buffering to tmpfile
        $tmpfile = tmpfile();
        if ($tmpfile === false) {
            throw new RuntimeException('Failed to create temp file.');
        }

        ob_start(function (string $buffer) use ($tmpfile) {
            fwrite($tmpfile, $buffer);
            return '';
        }, 4096);

        // Starts loading content
        (function () use ($view, $data) {
            extract($data);
            include $view;
        })();

        // Ends output and creates stream
        ob_end_clean();
        fseek($tmpfile, 0);
        static::setSharedViewsPath(null);
        $stream = $this->streamFactory->createStreamFromResource($tmpfile);

        // Creates PSR-7 ResponseInterface
        $response = $this->responseFactory
            ->createResponse(http_response_code() ?: 200)
            ->withBody($stream);

        // Assign all new headers to response
        $headers = static::getRenderHeaders();
        foreach ($headers as $hname => $hcontent) {
            $hcontent = array_map(function ($v) {
                return trim($v);
            }, explode(',', $hcontent));
            $response = $response->withHeader(trim($hname), $hcontent);
        }

        // Restores status and headers on server
        $this->restoreServerState();
        return $response;
    }

    public function renderJson($data = [])
    {
        $stream = $this->streamFactory->createStream(json_encode($data));
        return $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
    }
}
