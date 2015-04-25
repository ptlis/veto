<?php
/**
 * Veto.
 * PHP Microframework.
 *
 * @author brian ridley <ptlis@ptlis.net>
 * @copyright Damien Walsh 2013-2015
 * @version 0.1
 * @package veto
 */

namespace Veto\Tests\HTTP\Request;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Veto\Collection\Bag;
use Veto\HTTP\HeaderBag;
use Veto\HTTP\MessageBody;
use Veto\HTTP\Request;
use Veto\HTTP\UploadedFile;
use Veto\HTTP\Uri;

class CreateRequestFromEnvironmentTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateSimple()
    {
        $_SERVER = array(
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/foo/bar',
            'HTTP_HOST' => 'example.com',
            'HTTP_ACCEPT' => 'text/html,text/json'
        );
        $_COOKIE = array(
            'foo' => 'bar'
        );
        $_GET = array(
            'baz' => 'bat'
        );
        $_POST = array();

        $request = Request::createFromEnvironment();

        $this->validateRequest(
            $request,
            array(
                'protocol_version' => '1.1',
                'method' => 'GET',
                'uri' => new Uri('http', 'example.com', null, '/foo/bar'),
                'headers' => array(
                    'Host' => array(
                        'example.com'
                    ),
                    'Accept' => array(
                        'text/html,text/json'
                    )
                ),
                'server_params' => array(
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/foo/bar',
                    'HTTP_HOST' => 'example.com',
                    'HTTP_ACCEPT' => 'text/html,text/json'
                ),
                'cookie_params' => array(
                    'foo' => 'bar'
                ),
                'query_params' => array(
                    'baz' => 'bat'
                ),
                'parsed_body' => array()
            )
        );
    }

    public function testCreateSimpleHttp11()
    {
        $_SERVER = array(
            'SERVER_PROTOCOL' => 'HTTP/1.0',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/foo/bar',
            'HTTP_HOST' => 'example.com',
            'HTTP_ACCEPT' => 'text/html,text/json'
        );
        $_COOKIE = array(
            'foo' => 'bar'
        );
        $_GET = array(
            'baz' => 'bat'
        );
        $_POST = array();

        $request = Request::createFromEnvironment();

        $this->validateRequest(
            $request,
            array(
                'protocol_version' => '1.0',
                'method' => 'GET',
                'uri' => new Uri('http', 'example.com', null, '/foo/bar'),
                'headers' => array(
                    'Host' => array(
                        'example.com'
                    ),
                    'Accept' => array(
                        'text/html,text/json'
                    )
                ),
                'server_params' => array(
                    'SERVER_PROTOCOL' => 'HTTP/1.0',
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/foo/bar',
                    'HTTP_HOST' => 'example.com',
                    'HTTP_ACCEPT' => 'text/html,text/json'
                ),
                'cookie_params' => array(
                    'foo' => 'bar'
                ),
                'query_params' => array(
                    'baz' => 'bat'
                ),
                'parsed_body' => array()
            )
        );
    }

    public function testCreateSimpleUploadedFiles()
    {
        $_SERVER = array(
            'SERVER_PROTOCOL' => 'HTTP/1.0',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/foo/bar',
            'HTTP_HOST' => 'example.com',
            'HTTP_ACCEPT' => 'text/html,text/json'
        );
        $_COOKIE = array(
            'foo' => 'bar'
        );
        $_GET = array(
            'baz' => 'bat'
        );
        $_POST = array();
        $_FILES = array(
            'my_file' => array(
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/foo/bar',
                'error' => 0,
                'size' => 514123
            )
        );

        $request = Request::createFromEnvironment();

        $this->validateRequest(
            $request,
            array(
                'protocol_version' => '1.0',
                'method' => 'GET',
                'uri' => new Uri('http', 'example.com', null, '/foo/bar'),
                'headers' => array(
                    'Host' => array(
                        'example.com'
                    ),
                    'Accept' => array(
                        'text/html,text/json'
                    )
                ),
                'server_params' => array(
                    'SERVER_PROTOCOL' => 'HTTP/1.0',
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/foo/bar',
                    'HTTP_HOST' => 'example.com',
                    'HTTP_ACCEPT' => 'text/html,text/json'
                ),
                'cookie_params' => array(
                    'foo' => 'bar'
                ),
                'query_params' => array(
                    'baz' => 'bat'
                ),
                'parsed_body' => array(),
                'files' => array(
                    'my_file' => new UploadedFile(
                        new MountManager(array('local' => new Filesystem(new Local('/')))),
                        '/tmp/foo/bar',
                        514123,
                        0,
                        'test.jpg',
                        'image/jpeg'
                    )
                )
            )
        );
    }

    private function validateRequest(Request $request, array $expectedValues)
    {
        if (array_key_exists('method', $expectedValues)) {
            $this->assertEquals($expectedValues['method'], $request->getMethod());
        }

        if (array_key_exists('protocol_version', $expectedValues)) {
            $this->assertEquals($expectedValues['uri'], $request->getUri());
        }

        if (array_key_exists('protocol_version', $expectedValues)) {
            $this->assertEquals($expectedValues['protocol_version'], $request->getProtocolVersion());
        }

        if (array_key_exists('headers', $expectedValues)) {
            foreach ($expectedValues['headers'] as $name => $valueList) {
                $this->assertTrue($request->hasHeader($name));
                $this->assertEquals($valueList, $request->getHeader($name));
                $this->assertEquals(implode(',', $valueList), $request->getHeaderLine($name));
            }

            $this->assertSame(
                $expectedValues['headers'],
                $request->getHeaders()
            );
        }

        if (array_key_exists('server_params', $expectedValues)) {
            $this->assertEquals($expectedValues['server_params'], $request->getServerParams());
        }

        if (array_key_exists('cookie_params', $expectedValues)) {
            $this->assertEquals($expectedValues['cookie_params'], $request->getCookieParams());
        }

        if (array_key_exists('query_params', $expectedValues)) {
            $this->assertEquals($expectedValues['query_params'], $request->getQueryParams());
        }

        if (array_key_exists('parsed_body', $expectedValues)) {
            $this->assertEquals($expectedValues['parsed_body'], $request->getParsedBody());
        }

        if (array_key_exists('files', $expectedValues)) {
            $this->assertEquals($expectedValues['files'], $request->getUploadedFiles());
        }

        $this->assertInstanceOf(
            '\Psr\Http\Message\StreamInterface',
            $request->getBody()
        );
    }
}
