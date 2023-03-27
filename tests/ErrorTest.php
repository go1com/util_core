<?php

namespace go1\util\schema\tests;

use Assert\Assertion;
use go1\util\Error;
use go1\util\tests\UtilCoreTestCase;

class ErrorTest extends UtilCoreTestCase
{
    public function testWithNullData()
    {
        $jsonResponse = Error::formatError(null);
        $this->assertEquals($jsonResponse, []);
    }

    /**
     * @dataProvider provideErrorData
     */
    public function testFormatError(array $expectedOutput, array $input)
    {
        $jsonResponse = Error::formatError($input);
        $this->assertEquals($jsonResponse, $expectedOutput);
    }

    public function provideErrorData(): array
    {
        $error1 = [
            'message' => 'Invalid Id'
        ];
        $error2 = [
            'message' => 'Invalid Id',
            'error_code' => 'invalid_id',
            'ref' => 1234
        ];
        $error3 = array_merge($error2, ['error' => [[
            'message' => 'invalid lo',
            'path' => 'lo_id',
            'error_code' => 'invalid_lo',
            'http_code' => 400,
            'ref' => 4567
        ]]]);
        $error3Expected = array_merge(
            $error2,
            ['additional_errors' => $error3['error']]
        );

        return [
            'with only message' => [$error1, $error1],
            'with all parameters' => [$error2, $error2],
            'with additional errors' => [$error3Expected, $error3]
        ];
    }
}
