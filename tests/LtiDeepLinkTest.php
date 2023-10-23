<?php

namespace Tests;

use DOMDocument;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Mockery;
use Packback\Lti1p3\Interfaces\ILtiRegistration;
use Packback\Lti1p3\LtiConstants;
use Packback\Lti1p3\LtiDeepLink;
use Packback\Lti1p3\LtiDeepLinkResource;

class LtiDeepLinkTest extends TestCase
{
    public const CLIENT_ID = 'client-id';
    public const ISSUER = 'issuer';
    public const DEPLOYMENT_ID = 'deployment-id';
    public const LTI_RESOURCE_ARRAY = ['resource'];
    private $registrationMock;
    private $ltiResourceMock;

    protected function setUp(): void
    {
        $this->registrationMock = Mockery::mock(ILtiRegistration::class);
        $this->ltiResourceMock = Mockery::mock(LtiDeepLinkResource::class);
    }

    public function testItInstantiates()
    {
        $registration = Mockery::mock(ILtiRegistration::class);

        $deepLink = new LtiDeepLink($registration, 'test', []);

        $this->assertInstanceOf(LtiDeepLink::class, $deepLink);
    }

    public function testItGetsJwtResponse()
    {
        $this->setupMocksExpectations();

        $deepLink = new LtiDeepLink($this->registrationMock, self::DEPLOYMENT_ID, []);

        $result = $deepLink->getResponseJwt([$this->ltiResourceMock]);

        $publicKey = new Key(file_get_contents(__DIR__.'/data/public.key'), 'RS256');
        $resultPayload = JWT::decode($result, $publicKey);

        $this->assertEquals(self::CLIENT_ID, $resultPayload->iss);
        $this->assertEquals([self::ISSUER], $resultPayload->aud);
        $this->assertEquals($resultPayload->exp, $resultPayload->iat + 600);
        $this->assertStringStartsWith('nonce-', $resultPayload->nonce);
        $this->assertEquals(self::DEPLOYMENT_ID, $resultPayload->{LtiConstants::DEPLOYMENT_ID});
        $this->assertEquals(LtiConstants::MESSAGE_TYPE_DEEPLINK_RESPONSE, $resultPayload->{LtiConstants::MESSAGE_TYPE});
        $this->assertEquals(LtiConstants::V1_3, $resultPayload->{LtiConstants::VERSION});
        $this->assertEquals([self::LTI_RESOURCE_ARRAY], $resultPayload->{LtiConstants::DL_CONTENT_ITEMS});
    }

    public function testJwtResponseDoesNotContainDataPropertyWhenNotSet()
    {
        $this->setupMocksExpectations();

        $deepLink = new LtiDeepLink($this->registrationMock, self::DEPLOYMENT_ID, []);

        $result = $deepLink->getResponseJwt([$this->ltiResourceMock]);

        $publicKey = new Key(file_get_contents(__DIR__.'/data/public.key'), 'RS256');
        $resultPayload = JWT::decode($result, $publicKey);

        $this->assertArrayNotHasKey(LtiConstants::DL_DATA, get_object_vars($resultPayload));
    }

    public function testJwtResponseContainsDataPropertyWhenSet()
    {
        $this->setupMocksExpectations();

        $dataValue = 'value';

        $deepLink = new LtiDeepLink($this->registrationMock, self::DEPLOYMENT_ID, [
            'data' => $dataValue,
        ]);

        $result = $deepLink->getResponseJwt([$this->ltiResourceMock]);

        $publicKey = new Key(file_get_contents(__DIR__.'/data/public.key'), 'RS256');
        $resultPayload = JWT::decode($result, $publicKey);

        $this->assertEquals($dataValue, $resultPayload->{LtiConstants::DL_DATA});
    }

    public function testItGeneratesResponseForm()
    {
        $resources = [$this->ltiResourceMock];

        $deepLinkReturnUrl = 'https://example.com/return';
        $deepLinkArgs = [
            Mockery::mock(ILtiRegistration::class),
            self::DEPLOYMENT_ID,
            ['deep_link_return_url' => $deepLinkReturnUrl],
        ];
        $responseJwt = 'example-jwt';
        $deepLink = Mockery::mock(LtiDeepLink::class, $deepLinkArgs)->makePartial()
            ->shouldReceive('getResponseJwt')
            ->with($resources)
            ->once()
            ->andReturn($responseJwt)
            ->getMock();

        // The method directly echoes HTML output without returning it,
        // so the only way to capture content is through an output buffer
        ob_start();
        $deepLink->outputResponseForm($resources);
        $result = ob_get_contents();
        ob_end_clean();

        // This is required because the method does not output a well-formed HTML/XML document
        $xmlWrapperTag = 'body';

        $resultDocument = new DOMDocument();
        $resultDocument->loadXML("<{$xmlWrapperTag}>{$result}</{$xmlWrapperTag}>");

        $expectedDocument = new DOMDocument();

        $wrapperElement = $expectedDocument->createElement($xmlWrapperTag);

        $formElement = $expectedDocument->createElement('form');
        $formElement->setAttribute('id', 'auto_submit');
        $formElement->setAttribute('action', $deepLinkReturnUrl);
        $formElement->setAttribute('method', 'POST');

        $jwtInputElement = $expectedDocument->createElement('input');
        $jwtInputElement->setAttribute('type', 'hidden');
        $jwtInputElement->setAttribute('name', 'JWT');
        $jwtInputElement->setAttribute('value', $responseJwt);
        $formElement->appendChild($jwtInputElement);

        $submitInputElement = $expectedDocument->createElement('input');
        $submitInputElement->setAttribute('type', 'submit');
        $submitInputElement->setAttribute('name', 'Go');
        $formElement->appendChild($submitInputElement);

        $wrapperElement->appendChild($formElement);

        $scriptElement = $expectedDocument->createElement('script', "document.getElementById('auto_submit').submit();");
        $wrapperElement->appendChild($scriptElement);

        $expectedDocument->appendChild($wrapperElement);

        $this->assertXmlStringEqualsXmlString($expectedDocument->saveXML(), $resultDocument->saveXML());
    }

    private function setupMocksExpectations(): void
    {
        $this->registrationMock
            ->shouldReceive('getClientId')
            ->once()
            ->andReturn(self::CLIENT_ID);
        $this->registrationMock
            ->shouldReceive('getIssuer')
            ->once()
            ->andReturn(self::ISSUER);
        $this->registrationMock
            ->shouldReceive('getToolPrivateKey')
            ->once()
            ->andReturn(file_get_contents(__DIR__.'/data/private.key'));
        $this->registrationMock
            ->shouldReceive('getKid')
            ->once()
            ->andReturn('kid');

        $this->ltiResourceMock
            ->shouldReceive('toArray')
            ->once()
            ->andReturn(self::LTI_RESOURCE_ARRAY);
    }
}
