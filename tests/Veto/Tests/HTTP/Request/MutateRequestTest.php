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
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Veto\Collection\Bag;
use Veto\HTTP\HeaderBag;
use Veto\HTTP\MessageBody;
use Veto\HTTP\Request;
use Veto\HTTP\UploadedFile;
use Veto\HTTP\Uri;

class MutateRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testWithProtocolVersion()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withProtocolVersion('1.0');

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            '1.0',
            $newRequest->getProtocolVersion()
        );
    }

    public function testWithHeader()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withHeader('Accept-Language', 'en-gb,en-us;q=0.75');

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            'en-gb,en-us;q=0.75',
            $newRequest->getHeaderLine('Accept-Language')
        );
    }

    public function testWithAddedHeader()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withAddedHeader('X-Multi-Foo', 'baz');

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            array(
                'bar',
                'baz'
            ),
            $newRequest->getHeader('X-Multi-Foo')
        );
    }

    public function testWithoutHeader()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withoutHeader('X-Multi-Foo');

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertFalse(
            $newRequest->hasHeader('X-Multi-Foo')
        );
    }

    public function testWithBody()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withBody(new MessageBody(fopen('php://input', 'r')));

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );
    }

    public function testWithMethod()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withMethod('POST');

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            'POST',
            $newRequest->getMethod()
        );
    }

    public function testWithUriSameDomain()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withUri(
            new Uri('http', 'example.com', null, '/foo/bar/123')
        );

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            'http://example.com/foo/bar/123',
            strval($newRequest->getUri())
        );
    }

    public function testWithUriDifferentDomainPreserve()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withUri(
            new Uri('http', 'localhost', null, '/foo/bar/123'),
            true
        );

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            'localhost',
            $newRequest->getUri()->getHost()
        );

        $this->assertEquals(
            'example.com',
            $newRequest->getHeaderLine('Host')
        );
    }

    public function testWithUriDomainOmitted()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withUri(
            new Uri('http', '', null, '/foo/bar/123'),
            true
        );

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            'example.com',
            $newRequest->getUri()->getHost()
        );

        $this->assertEquals(
            'example.com',
            $newRequest->getHeaderLine('Host')
        );
    }

    public function testWithCookieParams()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withCookieParams(array(
            'session_id' => 'bar'
        ));

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            array(
                'session_id' => 'bar'
            ),
            $newRequest->getCookieParams()
        );
    }

    public function testWithQueryParams()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withQueryParams(array(
            'page' => '15'
        ));

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            array(
                'page' => '15'
            ),
            $newRequest->getQueryParams()
        );
    }

    public function testWithUploadedFiles()
    {
        $uploadedFiles = array(
            'my_file' => new UploadedFile(
                new MountManager(array('local' => new Filesystem(new NullAdapter()))),
                '/tmp/foo/bar',
                100,
                UPLOAD_ERR_OK,
                'foo.jpg',
                'text/plain'
            )
        );

        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withUploadedFiles($uploadedFiles);


        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            $uploadedFiles,
            $newRequest->getUploadedFiles()
        );
    }

    public function testWithAttributes()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withAttribute('foo', 'bar');

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            array('baz' => 'bat', 'foo' => 'bar'),
            $newRequest->getAttributes()
        );

        $this->assertEquals(
            'bar',
            $newRequest->getAttribute('foo')
        );
    }

    public function testWithoutAttributes()
    {
        $originalRequest = $this->getBaseRequest();
        $newRequest = $originalRequest->withoutAttribute('baz');

        $this->assertNotSame(
            $originalRequest,
            $newRequest
        );

        $this->assertEquals(
            array(),
            $newRequest->getAttributes()
        );

        $this->assertEquals(
            '',
            $newRequest->getAttribute('baz')
        );
    }

    private function getBaseRequest()
    {
        return new Request(
            'GET',
            new Uri('http', 'example.com', null, '/foo/bar'),
            new HeaderBag(array(
                'Host' => 'example.com',
                'X-Multi-Foo' => 'bar'
            )),
            new Bag(),
            new Bag(array(
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/foo/bar',
                'HTTP_HOST' => 'example.com',
                'HTTP_ACCEPT' => 'text/html,text/json'
            )),
            new Bag(),
            new Bag(),
            new Bag(),
            new Bag(array('baz' => 'bat')),
            new MessageBody(fopen('php://input', 'r')),
            '1.1'
        );
    }
}
