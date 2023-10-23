<?php

namespace Tests;

use Packback\Lti1p3\LtiDeepLinkResourceIframe;

class LtiDeepLinkResourceIframeTest extends TestCase
{
    public const INITIAL_WIDTH = 1;
    public const INITIAL_HEIGHT = 2;
    private LtiDeepLinkResourceIframe $ltiDeepLinkResourceIframe;

    public function setUp(): void
    {
        $this->ltiDeepLinkResourceIframe = new LtiDeepLinkResourceIframe(self::INITIAL_WIDTH, self::INITIAL_HEIGHT);
    }

    public function testItInstantiates()
    {
        $this->assertInstanceOf(LtiDeepLinkResourceIframe::class, $this->ltiDeepLinkResourceIframe);
    }

    public function testItCreatesANewInstance()
    {
        $deepLinkResource = LtiDeepLinkResourceIframe::new();

        $this->assertInstanceOf(LtiDeepLinkResourceIframe::class, $deepLinkResource);
    }

    public function testItGetsWidth()
    {
        $result = $this->ltiDeepLinkResourceIframe->getWidth();

        $this->assertEquals(self::INITIAL_WIDTH, $result);
    }

    public function testItSetsWidth()
    {
        $expected = 300;

        $result = $this->ltiDeepLinkResourceIframe->setWidth($expected);

        $this->assertSame($this->ltiDeepLinkResourceIframe, $result);
        $this->assertEquals($expected, $this->ltiDeepLinkResourceIframe->getWidth());
    }

    public function testItGetsHeight()
    {
        $result = $this->ltiDeepLinkResourceIframe->getHeight();

        $this->assertEquals(self::INITIAL_HEIGHT, $result);
    }

    public function testItSetsHeight()
    {
        $expected = 400;

        $result = $this->ltiDeepLinkResourceIframe->setHeight($expected);

        $this->assertSame($this->ltiDeepLinkResourceIframe, $result);
        $this->assertEquals($expected, $this->ltiDeepLinkResourceIframe->getHeight());
    }

    public function testItCreatesArrayWithoutOptionalProperties()
    {
        $this->ltiDeepLinkResourceIframe->setWidth(null);
        $this->ltiDeepLinkResourceIframe->setHeight(null);

        $result = $this->ltiDeepLinkResourceIframe->toArray();

        $this->assertEquals([], $result);
    }

    public function testItCreatesArrayWithDefinedOptionalProperties()
    {
        $expected = [
            'width' => 100,
            'height' => 200,
        ];

        $this->ltiDeepLinkResourceIframe->setWidth($expected['width']);
        $this->ltiDeepLinkResourceIframe->setHeight($expected['height']);

        $result = $this->ltiDeepLinkResourceIframe->toArray();

        $this->assertEquals($expected, $result);
    }
}
