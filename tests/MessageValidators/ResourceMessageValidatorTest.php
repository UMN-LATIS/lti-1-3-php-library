<?php

namespace Tests\MessageValidators;

use Packback\Lti1p3\LtiConstants;
use Packback\Lti1p3\LtiException;
use Packback\Lti1p3\MessageValidators\ResourceMessageValidator;
use Tests\TestCase;

class ResourceMessageValidatorTest extends TestCase
{
    public function testItCanValidate()
    {
        $this->assertTrue(ResourceMessageValidator::canValidate(static::validJwtBody()));
    }

    public function testItCannotValidate()
    {
        $jwtBody = static::validJwtBody();
        $jwtBody[LtiConstants::MESSAGE_TYPE] = 'some other type';

        $this->assertFalse(ResourceMessageValidator::canValidate($jwtBody));
    }

    public function testJwtBodyIsValid()
    {
        $this->assertNull(ResourceMessageValidator::validate(static::validJwtBody()));
    }

    public function testJwtBodyIsInvalidMissingSub()
    {
        $jwtBody = static::validJwtBody();
        $jwtBody['sub'] = '';

        $this->expectException(LtiException::class);

        ResourceMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidMissingLtiVersion()
    {
        $jwtBody = static::validJwtBody();
        unset($jwtBody[LtiConstants::VERSION]);

        $this->expectException(LtiException::class);

        ResourceMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidWrongLtiVersion()
    {
        $jwtBody = static::validJwtBody();
        $jwtBody[LtiConstants::VERSION] = '1.2.0';

        $this->expectException(LtiException::class);

        ResourceMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidMissingRoles()
    {
        $jwtBody = static::validJwtBody();
        unset($jwtBody[LtiConstants::ROLES]);

        $this->expectException(LtiException::class);

        ResourceMessageValidator::validate($jwtBody);
    }

    public function testJwtBodyIsInvalidMissingResourceLinkId()
    {
        $jwtBody = static::validJwtBody();
        unset($jwtBody[LtiConstants::RESOURCE_LINK]['id']);

        $this->expectException(LtiException::class);

        ResourceMessageValidator::validate($jwtBody);
    }

    private static function validJwtBody()
    {
        return [
            'sub' => 'subscriber',
            LtiConstants::MESSAGE_TYPE => ResourceMessageValidator::getMessageType(),
            LtiConstants::VERSION => LtiConstants::V1_3,
            LtiConstants::ROLES => [],
            LtiConstants::RESOURCE_LINK => [
                'id' => 'unique-id',
            ],
        ];
    }
}
