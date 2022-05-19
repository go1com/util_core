<?php

namespace go1\util\tests\model;

use go1\util\model\FilteredObjectTrait;
use go1\util\tests\UtilCoreTestCase;

class FilteredObjectTraitTest extends UtilCoreTestCase
{
    use FilteredObjectTrait;

    /**
     * FilteredObjectTrait should remove null properties
     */
    public function testExcludeNull()
    {
        $inputArray = [
            'first'  => 1,
            'second' => null,
            'third'  => 'three',
        ];

        $outputArray = $this->getFilteredObject($inputArray);
        $this->assertEquals((object)[
            'first' => 1,
            'third' => 'three'
        ], $outputArray);
    }

    /**
     * FilteredObjectTrait should not remove null properties if in allowNullProperties
     */
    public function testAllowNull()
    {
        $this->allowNullProperties = ['second'];

        $inputArray = [
            'first' => 1,
            'second' => null,
            'third' => 'three',
        ];

        $outputArray = $this->getFilteredObject($inputArray);
        $this->assertEquals((object)[
            'first'  => 1,
            'second' => null,
            'third'  => 'three'
        ], $outputArray);
    }
}
