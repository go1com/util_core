<?php

namespace go1\util\schema\tests;

use Assert\Assertion;
use go1\util\Error;
use go1\util\tests\UtilCoreTestCase;

class ErrorTest  extends UtilCoreTestCase
{
    public function testFormatError()
    {
        $jsonResponse = Error::formatError(null);
        $this->assertEquals($jsonResponse, []);

        $errors = ['message' => 'Invalid Id'];
        $jsonResponse = Error::formatError($errors);
        $this->assertEquals($jsonResponse, ['message' => $errors['message']]);

        $errors = ['message' => 'Invalid Id', 'error_code' => 'invalid_id', 'ref' => 1234];
        $jsonResponse = Error::formatError($errors);
        $this->assertEquals($jsonResponse, ['message' => $errors['message'], 'error_code' => $errors['error_code'], 'ref' => $errors['ref']]);

        $errors['error'][] = ['message' => 'invalid lo', 'path' => 'lo_id', 'error_code' => 'invalid_lo', 'http_code' => 400, 'ref' => 4567];
        $jsonResponse = Error::formatError($errors);
        $this->assertEquals($jsonResponse, ['additional_errors' => $errors['error'], 'message' => $errors['message'], 'error_code' => $errors['error_code'], 'ref' => $errors['ref']]);
    }
}
