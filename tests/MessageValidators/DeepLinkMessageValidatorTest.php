<?php

namespace Tests\MessageValidators;

use Packback\Lti1p3\LtiConstants;
use Packback\Lti1p3\LtiException;
use Packback\Lti1p3\MessageValidators\DeepLinkMessageValidator;
use Tests\TestCase;

class DeepLinkMessageValidatorTest extends TestCase
{
    public function testItCanValidate()
    {
        $this->assertTrue(DeepLinkMessageValidator::canValidate(static::validJwtBody()));
    }

    public function testItCannotValidate()
    {
        $jwtBody = static::validJwtBody();
        $jwtBody[LtiConstants::MESSAGE_TYPE] = 'some other type';

        $this->assertFalse(DeepLinkMessageValidator::canValidate($jwtBody));
    }

    public function testJwtBodyIsValid()
    {
        $this->assertNull(DeepLinkMessageValidator::validate(static::validJwtBody()));
    }

    public function testJwtBodyIsInvalidMissingSub()
    {
        $jwtBody = static::validJwtBody();
        $jwtBody['sub'] = '';

        $this->expectException(LtiException::class);

        DeepLinkMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidMissingLtiVersion()
    {
        $jwtBody = static::validJwtBody();
        unset($jwtBody[LtiConstants::VERSION]);

        $this->expectException(LtiException::class);

        DeepLinkMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidWrongLtiVersion()
    {
        $jwtBody = static::validJwtBody();
        $jwtBody[LtiConstants::VERSION] = '1.2.0';

        $this->expectException(LtiException::class);

        DeepLinkMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidMissingRoles()
    {
        $jwtBody = static::validJwtBody();
        unset($jwtBody[LtiConstants::ROLES]);

        $this->expectException(LtiException::class);

        DeepLinkMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidMissingDeepLinkSetting()
    {
        $jwtBody = static::validJwtBody();
        unset($jwtBody[LtiConstants::DL_DEEP_LINK_SETTINGS]);

        $this->expectException(LtiException::class);

        DeepLinkMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidMissingDeepLinkReturnUrl()
    {
        $jwtBody = static::validJwtBody();
        unset($jwtBody[LtiConstants::DL_DEEP_LINK_SETTINGS]['deep_link_return_url']);

        $this->expectException(LtiException::class);

        DeepLinkMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidMissingAcceptType()
    {
        $jwtBody = static::validJwtBody();
        unset($jwtBody[LtiConstants::DL_DEEP_LINK_SETTINGS]['accept_types']);

        $this->expectException(LtiException::class);

        DeepLinkMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidAcceptTypeIsInvalid()
    {
        $jwtBody = static::validJwtBody();
        $jwtBody[LtiConstants::DL_DEEP_LINK_SETTINGS]['accept_types'] = [];

        $this->expectException(LtiException::class);

        DeepLinkMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidMissingPresentation()
    {
        $jwtBody = static::validJwtBody();
        unset($jwtBody[LtiConstants::DL_DEEP_LINK_SETTINGS]['accept_presentation_document_targets']);

        $this->expectException(LtiException::class);

        DeepLinkMessageValidator::validate($jwtBody);
    }

    private static function validJwtBody()
    {
        return [
            'sub' => 'subscriber',
            LtiConstants::MESSAGE_TYPE => DeepLinkMessageValidator::getMessageType(),
            LtiConstants::VERSION => LtiConstants::V1_3,
            LtiConstants::ROLES => [],
            LtiConstants::DL_DEEP_LINK_SETTINGS => [
                'deep_link_return_url' => 'https://example.com',
                'accept_types' => ['ltiResourceLink'],
                'accept_presentation_document_targets' => ['iframe'],
            ],
        ];
    }
}
