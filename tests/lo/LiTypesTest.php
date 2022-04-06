<?php

namespace go1\util\tests\lo;

use go1\util\lo\LiTypes;
use go1\util\tests\UtilCoreTestCase;

class LiTypesTest extends UtilCoreTestCase
{
    public function testAllLiTypes()
    {
        $all = LiTypes::all();
        $this->assertContains(LiTypes::ASSIGNMENT, $all);
        $this->assertContains(LiTypes::AUDIO, $all);
        $this->assertContains(LiTypes::DOCUMENT, $all);
        $this->assertContains(LiTypes::EVENT, $all);
        $this->assertContains(LiTypes::H5P, $all);
        $this->assertContains(LiTypes::INTEGRATION, $all);
        $this->assertContains(LiTypes::INTERACTIVE, $all);
        $this->assertContains(LiTypes::LINK, $all);
        $this->assertContains(LiTypes::LTI, $all);
        $this->assertContains(LiTypes::QUESTION, $all);
        $this->assertContains(LiTypes::QUIZ, $all);
        $this->assertContains(LiTypes::TEXT, $all);
        $this->assertContains(LiTypes::VIDEO, $all);
        $this->assertContains(LiTypes::ACTIVITY, $all);
        $this->assertContains(LiTypes::ATTENDANCE, $all);
        $this->assertContains(LiTypes::IFRAME, $all);
        $this->assertContains(LiTypes::MANUAL, $all);
        $this->assertContains(LiTypes::WORKSHOP, $all);
        $this->assertContains(LiTypes::RESOURCE, $all);
        $this->assertEquals(19, sizeof($all));
    }
}
