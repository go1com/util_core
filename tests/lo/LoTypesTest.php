<?php

namespace go1\util\tests\lo;

use go1\util\lo\LoTypes;
use go1\util\tests\UtilCoreTestCase;

class LoTypesTest extends UtilCoreTestCase
{
    public function testAllLoTypes()
    {
        $all = LoTypes::all();
        $this->assertContains(LoTypes::ACHIEVEMENT, $all);
        $this->assertContains(LoTypes::AWARD, $all);
        $this->assertContains(LoTypes::COURSE, $all);
        $this->assertContains(LoTypes::LEANING_PATHWAY, $all);
        $this->assertContains(LoTypes::MODULE, $all);
        $this->assertContains(LoTypes::GROUP, $all);
        $this->assertContains(LoTypes::PLAYLIST, $all);
        $this->assertEquals(7, sizeof($all));
    }
}
