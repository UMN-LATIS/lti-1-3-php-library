<?php

namespace Tests;

use DateTime;
use Packback\Lti1p3\LtiDeepLinkDateTimeInterval;
use Packback\Lti1p3\LtiException;

class LtiDeepLinkDateTimeIntervalTest extends TestCase
{
    private DateTime $initialStart;
    private DateTime $initialEnd;
    private LtiDeepLinkDateTimeInterval $ltiDeepLinkDateTimeInterval;

    public function setUp(): void
    {
        $this->initialStart = date_create();
        $this->initialEnd = date_create();
        $this->ltiDeepLinkDateTimeInterval = new LtiDeepLinkDateTimeInterval($this->initialStart, $this->initialEnd);
    }

    public function testItInstantiates()
    {
        $this->assertInstanceOf(LtiDeepLinkDateTimeInterval::class, $this->ltiDeepLinkDateTimeInterval);
    }

    public function testItCreatesANewInstance()
    {
        $deepLinkResource = LtiDeepLinkDateTimeInterval::new();

        $this->assertInstanceOf(LtiDeepLinkDateTimeInterval::class, $deepLinkResource);
    }

    public function testItGetsStart()
    {
        $result = $this->ltiDeepLinkDateTimeInterval->getStart();

        $this->assertEquals($this->initialStart, $result);
    }

    public function testItSetsStart()
    {
        $expected = date_create('+1 day');

        $result = $this->ltiDeepLinkDateTimeInterval->setStart($expected);

        $this->assertSame($this->ltiDeepLinkDateTimeInterval, $result);
        $this->assertEquals($expected, $this->ltiDeepLinkDateTimeInterval->getStart());
    }

    public function testItGetsEnd()
    {
        $result = $this->ltiDeepLinkDateTimeInterval->getEnd();

        $this->assertEquals($this->initialEnd, $result);
    }

    public function testItSetsEnd()
    {
        $expected = date_create('+1 day');

        $result = $this->ltiDeepLinkDateTimeInterval->setEnd($expected);

        $this->assertSame($this->ltiDeepLinkDateTimeInterval, $result);
        $this->assertEquals($expected, $this->ltiDeepLinkDateTimeInterval->getEnd());
    }

    public function testItThrowsExceptionWhenCreatingArrayWithBothPropertiesNull()
    {
        $this->ltiDeepLinkDateTimeInterval->setStart(null);
        $this->ltiDeepLinkDateTimeInterval->setEnd(null);

        $this->expectException(LtiException::class);
        $this->expectExceptionMessage('At least one of the interval bounds must be specified on the object instance');

        $this->ltiDeepLinkDateTimeInterval->toArray();
    }

    public function testItThrowsExceptionWhenCreatingArrayWithInvalidTimeInterval()
    {
        $this->ltiDeepLinkDateTimeInterval->setStart(date_create());
        $this->ltiDeepLinkDateTimeInterval->setEnd(date_create('-1 day'));

        $this->expectException(LtiException::class);
        $this->expectExceptionMessage('Interval start time cannot be greater than end time');

        $this->ltiDeepLinkDateTimeInterval->toArray();
    }

    public function testItCreatesArrayWithDefinedOptionalProperties()
    {
        $expectedStart = date_create('+1 day');
        $expectedEnd = date_create('+2 days');
        $expected = [
            'startDateTime' => $expectedStart->format(DateTime::ATOM),
            'endDateTime' => $expectedEnd->format(DateTime::ATOM),
        ];

        $this->ltiDeepLinkDateTimeInterval->setStart($expectedStart);
        $this->ltiDeepLinkDateTimeInterval->setEnd($expectedEnd);

        $result = $this->ltiDeepLinkDateTimeInterval->toArray();

        $this->assertEquals($expected, $result);
    }
}
