<?php

namespace go1\util\model;

use function is_object;

trait FilteredObjectTrait
{
    protected $allowNullProperties = [];

    /**
     * Filters out the null values and creates a plain object. If such object would be empty, returns null instead.
     *
     * @param array $values
     * @return \stdClass
     */
    protected function getFilteredObject(array $values): \stdClass
    {
        $plainEmptyObject = new \stdClass();

        return (object) array_filter(
            $values,
            function ($val, $key) use ($plainEmptyObject) {
                if (isset($val)) {
                    return !(is_object($val) && $val == $plainEmptyObject);
                }

                return in_array($key, $this->allowNullProperties);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }
}
