<?php

namespace go1\util\tests\error;

use go1\util\error\Relay;
use go1\util\tests\UtilCoreTestCase;

class RelayTest extends UtilCoreTestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $relay;
    private $response;
    private $exception;

    public function setUp() : void
    {
        $this->relay = $this->getMockForTrait(Relay::class);

        $this->response = new Class () {
            private $body;
            private $statusCode;
            private $headers;

            public function set($body, $statusCode, $headers)
            {
                $this->body = $body;
                $this->statusCode = $statusCode;
                $this->headers = $headers;
            }

            function getBody () {
                return $this->body;
            }
            function getStatusCode () {
                return $this->statusCode;
            }
            function getHeaders () {
                return $this->headers;
            }
        };

        $this->exception = new Class () extends \RuntimeException {
            private $response;

            function getResponse() {
                return $this->response;
            }
            function setResponse($response) {
                $this->response = $response;
            }
        };
    }

    /**
     * Relaying a BadResponseException object should return a valid JsonResponse object.
     */
    public function testRelayException()
    {
        $this->response->set(json_encode(['error' => 'foo']), 418, ['Content-Type' => 'application/json']);
        $this->exception->setResponse($this->response);

        $response = $this->relay->relayException($this->exception);
        $responseBody = json_decode($response->getContent(), true);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Response::class, $response);
        $this->assertEquals(418, $response->getStatusCode());
        $this->assertEquals('foo', $responseBody['error']);
    }

    /**
     * Relaying a BadResponseException object should return a valid JsonResponse object if content-type key in lower case.
     */
    public function testRelayExceptionWithLowerCaseContentType()
    {
        $this->response->set(json_encode(['error' => 'foo']), 418, ['content-type' => 'application/json']);
        $this->exception->setResponse($this->response);

        $response = $this->relay->relayException($this->exception);
        $responseBody = json_decode($response->getContent(), true);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Response::class, $response);
        $this->assertEquals(418, $response->getStatusCode());
        $this->assertEquals('foo', $responseBody['error']);
    }

    /**
     * Relaying a BadResponseException object should return a valid JsonResponse object if content-type does not exists.
     */
    public function testRelayExceptionWithNoContentType()
    {
        $this->response->set(json_encode(['error' => 'foo']), 418, []);
        $this->exception->setResponse($this->response);

        $response = $this->relay->relayException($this->exception);
        $responseBody = json_decode($response->getContent(), true);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Response::class, $response);
        $this->assertEquals(418, $response->getStatusCode());
        $this->assertEquals('foo', $responseBody['error']);
    }

    /**
     * Relaying a normal Exception object should return a valid 500 JsonResponse object
     */
    public function testRelayExceptionWithNoDefaultException()
    {
        $response = $this->relay->relayException(new \RuntimeException());

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }
}
