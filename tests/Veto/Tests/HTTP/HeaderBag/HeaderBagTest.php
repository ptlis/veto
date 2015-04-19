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

namespace Veto\Tests\HTTP\HeaderBag;

use Veto\Collection\Bag;
use Veto\HTTP\HeaderBag;

class HeaderBagTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $headerBag = new HeaderBag(array(
            'Foo' => array('bar'),
            'Baz-Bat' => 'Qux'
        ));

        $this->assertEquals(
            array(
                'Foo' => array('bar'),
                'Baz-Bat' => array('Qux')
            ),
            $headerBag->all()
        );
    }

    public function testCreateFromEnvironment()
    {
        $environment = new Bag(array(
            'HTTP_HOST' => 'example.com',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_USER_AGENT' => 'FakeBrowser',
            'HTTP_ACCEPT' => '*/*',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en-US;q=0.8,en;q=0.6'
        ));

        $expected = new HeaderBag(array(
            'Host' => 'example.com',
            'Connection' => 'keep-alive',
            'User-Agent' => 'FakeBrowser',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'en-GB,en-US;q=0.8,en;q=0.6'
        ));

        $this->assertEquals(
            $expected,
            HeaderBag::createFromEnvironment($environment)
        );
    }

    public function testNormalizeHasKey()
    {
        $headerBag = new HeaderBag(array(
            'foo-BAR' => 'value'
        ));

        $this->assertTrue(
            $headerBag->has('foo-bar')
        );
    }

    public function testNormalizeGetValue()
    {
        $headerBag = new HeaderBag(array(
            'foo-BAR' => 'value'
        ));

        // Value retrieval by key
        $this->assertEquals(
            array('value'),
            $headerBag->get('FOO-bAr')
        );
    }

    public function testNormalizeAdd()
    {
        $headerBag = new HeaderBag(array(
            'foo-BAR' => 'value'
        ));

        $headerBag->add('Foo-Bar', 'second value');

        $this->assertEquals(
            array('value', 'second value'),
            $headerBag->get('FOO-BAR')
        );
    }

    public function testNormalizeRemove()
    {
        $headerBag = new HeaderBag(array(
            'foo-BAR' => 'value'
        ));

        $prevValue = $headerBag->remove('FoO-BAr');

        $this->assertEquals(
            array(),
            $headerBag->get('FOO-BAR')
        );

        $this->assertEquals(
            array('value'),
            $prevValue
        );
    }

    public function testNormalizedInternalRepresentation()
    {
        $headerBag = new HeaderBag(array(
            'foo-BAR' => 'value',
            'Baz-baT' => 'second value'
        ));

        $this->assertEquals(
            array(
                'Foo-Bar' => array('value'),
                'Baz-Bat' => array('second value')
            ),
            $headerBag->all()
        );
    }

    public function testIterator()
    {
        $headerBag = new HeaderBag(array(
            'foo-BAR' => 'value',
            'Baz-baT' => 'second value'
        ));

        $count = 0;
        foreach ($headerBag as $name => $value) {
            $count++;
        }

        $this->assertEquals(
            2,
            $count
        );
    }
}
