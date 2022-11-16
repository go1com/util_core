<?php

namespace go1\util\tests\portal;

use go1\util\portal\PortalChecker;
use go1\util\portal\PortalHelper;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilCoreTestCase;
use go1\util\user\Roles;

class PortalCheckerTest extends UtilCoreTestCase
{
    use PortalMockTrait;

    public function testAllowPublicGroupFalse()
    {
        $instanceId = $this->createPortal($this->go1, [
            'title' => 'qa.mygo1.com',
            'data'  => json_encode([
                'configuration' => ['public_group' => 0],
            ]),
        ]);

        $portal = PortalHelper::load($this->go1, $instanceId);

        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertFalse($group);
    }

    public function testAllowPublicGroupTrue()
    {
        $instanceId = $this->createPortal($this->go1, [
            'title' => 'qa.mygo1.com',
            'data'  => json_encode([
                'configuration' => ['public_group' => 1],
            ]),
        ]);

        $portal = PortalHelper::load($this->go1, $instanceId);
        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertTrue($group);
    }

    public function testAllowPublicGroupTrueWithoutFieldPublicGroup()
    {
        $instanceId = $this->createPortal($this->go1, [
            'title' => 'qa.mygo1.com',
        ]);

        $portal = PortalHelper::load($this->go1, $instanceId);
        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertFalse($group);
    }

    public function testAllowPublicGroupEnableTrue()
    {
        $instanceId = $this->createPortal($this->go1, [
            'title' => 'qa.mygo1.com',
            'data'  => json_encode([
                'configuration' => ['publicGroupsEnabled' => 1],
            ]),
        ]);

        $portal = PortalHelper::load($this->go1, $instanceId);
        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertTrue($group);
    }

    public function testAllowPublicGroupEnableFalse()
    {
        $instanceId = $this->createPortal($this->go1, [
            'title' => 'qa.mygo1.com',
            'data'  => json_encode([
                'configuration' => ['publicGroupsEnabled' => 0],
            ]),
        ]);

        $portal = PortalHelper::load($this->go1, $instanceId);
        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertFalse($group);
    }

    public function dataBuildLink()
    {
        return [
            ['production', 'az.mygo1.com', '', 'p/#', 'https://az.mygo1.com/p/#/'],
            ['production', 'az.mygo1.com', '/', 'p/#', 'https://az.mygo1.com/p/#/'],
            ['production', 'public.mygo1.com', '', '', 'https://www.go1.com/'],
            ['production', 'az.mygo1.com', 'embed-course/12345/', 'p/embed.html#', 'https://az.mygo1.com/p/embed.html#/embed-course/12345/'],
            ['production', 'az.mygo1.com', '123', 'play', 'https://az.mygo1.com/play/123'],
            ['staging', 'az.mygo1.com', '', 'p/#', 'https://az.mygo1.com/p/#/'],
            ['staging', 'az.mygo1.com', '123', 'play', 'https://az.mygo1.com/play/123'],
            ['qa', 'az.mygo1.com', '', 'p/#', 'https://az.mygo1.com/p/#/'],
            ['dev', 'az.mygo1.com', '', 'p/#', 'https://az.mygo1.com/p/#/'],
            ['', 'az.mygo1.com', '', 'p/#', 'https://az.mygo1.com/p/#/'],
        ];
    }

    /**
     * @dataProvider dataBuildLink
     */
    public function testBuildLink(string $env, string $instance, string $uri, string $prefix, string $expecting)
    {
        putenv("ENV=$env");
        $instanceId = $this->createPortal($this->go1, ['title' => $instance]);
        $portal = PortalHelper::load($this->go1, $instanceId);

        $this->assertEquals($expecting, (new PortalChecker)->buildLink($portal, $uri, $prefix));
    }

    public function testBuildLinkNoPublicDomainReplacement()
    {
        putenv('ENV=production');
        $instanceId = $this->createPortal($this->go1, ['title' => 'public.mygo1.com']);
        $portal = PortalHelper::load($this->go1, $instanceId);
        $this->assertEquals('https://public.mygo1.com/p/#/', (new PortalChecker)->buildLink($portal, '', 'p/#', false));
    }

    public function testAllowDiscussion()
    {
        $id = $this->createPortal($this->go1, [
            'title' => 'portal1.mygo1.com',
            'data'  => ['configuration' => ['discussion' => 0]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertFalse(PortalChecker::allowDiscussion($portal));

        $id = $this->createPortal($this->go1, [
            'title' => 'portal2.mygo1.com',
            'data'  => ['configuration' => ['discussion' => 1]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertTrue(PortalChecker::allowDiscussion($portal));

        $id = $this->createPortal($this->go1, [
            'title' => 'portal3.mygo1.com',
            'data'  => ['configuration' => ['discussionEnabled' => 0]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertFalse(PortalChecker::allowDiscussion($portal));

        $id = $this->createPortal($this->go1, ['title' => 'portal4.mygo1.com']);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertTrue(PortalChecker::allowDiscussion($portal));
    }

    public function testAllowUserInvite()
    {
        $id = $this->createPortal($this->go1, [
            'title' => 'portal1.mygo1.com',
            'data'  => ['configuration' => ['user_invite' => 0]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertFalse(PortalChecker::allowUserInvite($portal));

        $id = $this->createPortal($this->go1, [
            'title' => 'portal2.mygo1.com',
            'data'  => ['configuration' => ['user_invite' => 1]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertTrue(PortalChecker::allowUserInvite($portal));

        $id = $this->createPortal($this->go1, ['title' => 'portal3.mygo1.com']);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertTrue(PortalChecker::allowUserInvite($portal));
    }

    public function testAllowPublicProfile()
    {
        $id = $this->createPortal($this->go1, [
            'title' => 'portal1.mygo1.com',
            'data'  => ['configuration' => ['public_profiles' => 0]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertFalse(PortalChecker::allowPublicProfile($portal));

        $id = $this->createPortal($this->go1, [
            'title' => 'portal2.mygo1.com',
            'data'  => ['configuration' => ['public_profiles' => 1]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertTrue(PortalChecker::allowPublicProfile($portal));

        $id = $this->createPortal($this->go1, ['title' => 'portal3.mygo1.com']);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertFalse(PortalChecker::allowPublicProfile($portal));
    }

    public function testAllowUserPayment()
    {
        $id = $this->createPortal($this->go1, [
            'title' => 'portal1.mygo1.com',
            'data'  => ['configuration' => ['user_payment' => 0]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertFalse(PortalChecker::allowUserPayment($portal));

        $id = $this->createPortal($this->go1, [
            'title' => 'portal2.mygo1.com',
            'data'  => ['configuration' => ['user_payment' => 1]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertTrue(PortalChecker::allowUserPayment($portal));

        $id = $this->createPortal($this->go1, ['title' => 'portal3.mygo1.com']);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertTrue(PortalChecker::allowUserPayment($portal));
    }

    public function testAllowMarketplace()
    {
        $id = $this->createPortal($this->go1, [
            'title' => 'portal1.mygo1.com',
            'data'  => ['features' => ['marketplace' => 0]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertFalse(PortalChecker::allowMarketplace($portal));

        $id = $this->createPortal($this->go1, [
            'title' => 'portal2.mygo1.com',
            'data'  => ['features' => ['marketplace' => 1]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertTrue(PortalChecker::allowMarketplace($portal));

        $id = $this->createPortal($this->go1, ['title' => 'portal3.mygo1.com']);
        $portal = PortalHelper::load($this->go1, $id);
        $this->assertTrue(PortalChecker::allowMarketplace($portal));
    }

    public function notifyRemindConfig()
    {
        return [
            [
                [Roles::STUDENT => 1, Roles::ASSESSOR => 1, Roles::MANAGER => 1],
                [Roles::STUDENT => true, Roles::ASSESSOR => true, Roles::MANAGER => true, Roles::ADMIN => false],
            ],
            [
                [Roles::STUDENT => 0, Roles::ASSESSOR => 0, Roles::MANAGER => 0],
                [Roles::STUDENT => false, Roles::ASSESSOR => false, Roles::MANAGER => false],
            ],
            [
                [],
                [Roles::STUDENT => false, Roles::ASSESSOR => false, Roles::MANAGER => false],
            ],
            [
                [Roles::STUDENT => 1],
                [Roles::STUDENT => true, Roles::ASSESSOR => false, Roles::MANAGER => false],
            ],
            [
                [Roles::ASSESSOR => 1],
                [Roles::STUDENT => false, Roles::ASSESSOR => true, Roles::MANAGER => false],
            ],
            [
                [Roles::MANAGER => 1],
                [Roles::STUDENT => false, Roles::ASSESSOR => false, Roles::MANAGER => true],
            ],
        ];
    }

    /**
     * @dataProvider notifyRemindConfig
     */
    public function testAllowNotifyRemindMajorEventByRole($data, $expected)
    {
        $id = $this->createPortal($this->go1, [
            'title' => 'qa.mygo1.com',
            'data'  => ['configuration' => [PortalHelper::FEATURE_NOTIFY_REMIND_MAJOR_EVENT => $data]],
        ]);
        $portal = PortalHelper::load($this->go1, $id);
        foreach ($expected as $role => $assert) {
            $this->assertEquals($assert, PortalChecker::allowNotifyRemindMajorEventByRole($portal, $role));
        }
    }

    public function testAllowSendingWelcomeEmailWithPortalLegacy()
    {
        $dataPortal = [
            'data'    => [
                'files'         => ['logo' => 'http://portal.png'],
                'configuration' => ['foo' => '{"foo":"bar"}', 'send_welcome_email' => 1],
            ],
            'title'   => 'daitest.mygo1.com',
            'version' => 'v1.5.0',
        ];
        $portalId = $this->createPortal($this->go1, $dataPortal);
        $portal = PortalHelper::load($this->go1, $portalId);
        $portalChecker = new PortalChecker();
        $this->assertTrue($portalChecker->isLegacy($portal));
        $this->assertTrue($portalChecker->allowSendingWelcomeEmail($portal));
    }

    public function testAllowSendingWelcomeEmailWithDefaultConfig()
    {
        $dataPortal = [
            'data'    => [
                'files'         => ['logo' => 'http://portal.png'],
                'configuration' => ['foo' => '{"foo":"bar"}'],
            ],
            'title'   => 'daitest.mygo1.com',
            'version' => 'v1.5.0',
        ];
        $portalId = $this->createPortal($this->go1, $dataPortal);
        $portal = PortalHelper::load($this->go1, $portalId);
        $portalChecker = new PortalChecker();
        $this->assertTrue($portalChecker->isLegacy($portal));
        $this->assertTrue($portalChecker->allowSendingWelcomeEmail($portal));
    }

    public function testAllowSendingWelcomeEmailWithPortalLegacyButNotConfigSendMail()
    {
        $dataPortal = [
            'data'    => [
                'files'         => ['logo' => 'http://portal.png'],
                'configuration' => ['foo' => '{"foo":"bar"}', 'send_welcome_email' => 0],
            ],
            'title'   => 'daitest.mygo1.com',
            'version' => 'v1.5.0',
        ];
        $portalId = $this->createPortal($this->go1, $dataPortal);
        $portal = PortalHelper::load($this->go1, $portalId);
        $portalChecker = new PortalChecker();
        $this->assertTrue($portalChecker->isLegacy($portal));
        $this->assertFalse($portalChecker->allowSendingWelcomeEmail($portal));
    }

    public function testAllowSendingWelcomeEmailWithNewestVersionPortal()
    {
        $dataPortal = [
            'data'  => [
                'files'         => ['logo' => 'http://portal.png'],
                'configuration' => ['foo' => '{"foo":"bar"}'],
            ],
            'title' => 'daitest.mygo1.com',
        ];
        $portalId = $this->createPortal($this->go1, $dataPortal);
        $portal = PortalHelper::load($this->go1, $portalId);
        $portalChecker = new PortalChecker();
        $this->assertFalse($portalChecker->isLegacy($portal));
        $this->assertTrue($portalChecker->allowSendingWelcomeEmail($portal));
    }

    public function testAllowSendingWelcomeEmailWithConfigNewestVersionPortal()
    {
        $dataPortal = [
            'data'  => [
                'files'         => ['logo' => 'http://portal.png'],
                'configuration' => ['foo' => '{"foo":"bar"}', 'send_welcome_email' => 0],
            ],
            'title' => 'daitest.mygo1.com',
        ];
        $portalId = $this->createPortal($this->go1, $dataPortal);
        $portal = PortalHelper::load($this->go1, $portalId);
        $portalChecker = new PortalChecker();
        $this->assertFalse($portalChecker->isLegacy($portal));
        $this->assertTrue($portalChecker->allowSendingWelcomeEmail($portal));
    }

    public function testAllowNotifyEnrolmentWithLegacyPortal()
    {
        $dataPortal = [
            'data'    => [
                'files'         => ['logo' => 'http://portal.png'],
                'configuration' => ['foo' => '{"foo":"bar"}', 'notify_on_enrolment_create' => 1],
            ],
            'title'   => 'daitest.mygo1.com',
            'version' => 'v1.5.0',
        ];
        $portalId = $this->createPortal($this->go1, $dataPortal);
        $portal = PortalHelper::load($this->go1, $portalId);
        $portalChecker = new PortalChecker();
        $this->assertTrue($portalChecker->isLegacy($portal));
        $this->assertTrue($portalChecker->allowNotifyEnrolment($portal));
    }

    public function testNotAllowNotifyEnrolmentWithLegacyPortal()
    {
        $dataPortal = [
            'data'    => [
                'files'         => ['logo' => 'http://portal.png'],
                'configuration' => ['foo' => '{"foo":"bar"}', 'notify_on_enrolment_create' => 0],
            ],
            'title'   => 'daitest.mygo1.com',
            'version' => 'v1.5.0',
        ];
        $portalId = $this->createPortal($this->go1, $dataPortal);
        $portal = PortalHelper::load($this->go1, $portalId);
        $portalChecker = new PortalChecker();
        $this->assertTrue($portalChecker->isLegacy($portal));
        $this->assertFalse($portalChecker->allowNotifyEnrolment($portal));
    }

    public function testDefaultAllowNotifyEnrolmentWithLegacyPortal()
    {
        $dataPortal = [
            'data'    => [
                'files'         => ['logo' => 'http://portal.png'],
                'configuration' => ['foo' => '{"foo":"bar"}'],
            ],
            'title'   => 'daitest.mygo1.com',
            'version' => 'v1.5.0',
        ];
        $portalId = $this->createPortal($this->go1, $dataPortal);
        $portal = PortalHelper::load($this->go1, $portalId);
        $portalChecker = new PortalChecker();
        $this->assertTrue($portalChecker->isLegacy($portal));
        $this->assertTrue($portalChecker->allowNotifyEnrolment($portal));
    }

    public function testAllowNotifyEnrolmentWithNewestVersionPortal()
    {
        $dataPortal = [
            'data'  => [
                'files'         => ['logo' => 'http://portal.png'],
                'configuration' => ['foo' => '{"foo":"bar"}'],
            ],
            'title' => 'daitest.mygo1.com',
        ];
        $portalId = $this->createPortal($this->go1, $dataPortal);
        $portal = PortalHelper::load($this->go1, $portalId);
        $portalChecker = new PortalChecker();
        $this->assertFalse($portalChecker->isLegacy($portal));
        $this->assertTrue($portalChecker->allowNotifyEnrolment($portal));
    }

    public function testAllowNotifyEnrolmentWithConfigNewestVersionPortal()
    {
        $dataPortal = [
            'data'  => [
                'files'         => ['logo' => 'http://portal.png'],
                'configuration' => ['foo' => '{"foo":"bar"}', 'notify_on_enrolment_create' => 0],
            ],
            'title' => 'daitest.mygo1.com',
        ];
        $portalId = $this->createPortal($this->go1, $dataPortal);
        $portal = PortalHelper::load($this->go1, $portalId);
        $portalChecker = new PortalChecker();
        $this->assertFalse($portalChecker->isLegacy($portal));
        $this->assertTrue($portalChecker->allowNotifyEnrolment($portal));
    }

    public function dataForAvailablePortalContent()
    {
        return [
            [['free'], 1, true],
            [['free', 'subscribe'], 1, true],
            [['free', 'subscribe', 'paid'], 1, true],
            [['free', 'subscribe', 'paid', 'custom_share'], 1, true],
            [['subscribe'], 1, true],
            [['subscribe', 'paid'], 1, true],
            [['subscribe', 'paid', 'custom_share'], 1, true],
            [['paid'], 1, true],
            [['paid', 'custom_share'], 1, true],
            [['custom_share'], 1, false],
            [['free'], 0, false],
            [['paid'], 0, false],
            [['free', 'paid'], 0, false],
        ];
    }

    /** @dataProvider dataForAvailablePortalContent */
    public function testSelectedContentSelections(array $collections, int $allowMarketplace, bool $expected)
    {
        $dataPortal = [
            'data'  => [
                'files'         => ['logo' => 'http://portal.png'],
                'configuration' => ['foo' => '{"foo":"bar"}', 'collections' => $collections],
                'features'      => ['marketplace' => $allowMarketplace],
            ],
            'title' => 'daitest.mygo1.com',
        ];
        $portalId = $this->createPortal($this->go1, $dataPortal);
        $portal = PortalHelper::load($this->go1, $portalId);
        $this->assertEquals($expected, PortalChecker::selectedContentSelections($portal));
    }

    public function dataBuildLinkQA()
    {
        return [
            ['ENV_HOSTNAME=qa.go1.cloud', 'https://qa.go1.cloud/p/#/'],
            ['ENV_HOSTNAME', 'https://qa.go1.cloud/p/#/'],
        ];
    }

    public function testBuildLinkDefaultPrefix()
    {
        putenv("ENV=production");
        putenv("ENV_HOSTNAME_QA");
        $portalId = $this->createPortal($this->go1, ['title' => 'az.mygo1.com']);
        $portal = PortalHelper::load($this->go1, $portalId);
        $this->assertEquals('https://az.mygo1.com/p/#/app/course-overview/123', (new PortalChecker)->buildLink($portal, 'app/course-overview/123'));
    }

    public function testBuildLinkForMonolith()
    {
        putenv("MONOLITH=monolith");
        putenv("ENV_HOSTNAME=localhost");
        putenv("ENV_HOSTNAME_QA");
        $portalId = $this->createPortal($this->go1, ['title' => 'az.mygo1.com']);
        $portal = PortalHelper::load($this->go1, $portalId);
        $this->assertEquals('https://localhost/p/#/app/course-overview/123', (new PortalChecker)->buildLink($portal, 'app/course-overview/123'));
    }

    public function testBuildLinkForLegacy()
    {
        putenv("MONOLITH");

        $portalId = $this->createPortal($this->go1, [
            'title'   => 'az.mygo1.com',
            'version' => PortalHelper::LEGACY_VERSION,
        ]);
        $portal = PortalHelper::load($this->go1, $portalId);
        $this->assertEquals('https://az.mygo1.com/webapp/#/app/course-overview/123', (new PortalChecker)->buildLink($portal, 'app/course-overview/123'));
    }

    public function testBuildLinkWithoutPrefix()
    {
        $portalId = $this->createPortal($this->go1, ['title' => 'az.mygo1.com']);
        $portal = PortalHelper::load($this->go1, $portalId);
        $this->assertEquals('https://az.mygo1.com/foo', (new PortalChecker)->buildLink($portal, 'foo', ''));
    }
}
