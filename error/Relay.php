<?php

namespace go1\util\error;

use Symfony\Component\HttpFoundation\Response;

trait Relay
{
    /**
     * Relay a 4xx or 5xx response received from a service to the API consumer.
     * Preserves the original status code, payload and Content-Type header.
     *
     * Ideally the exception param should be a BadResponseException, any other type of exception
     * will return a 500 response
     *
     * @param \RuntimeException $e
     * @param int|null $overwriteErrorCode
     * @return Response
     */
    public function relayException(\RuntimeException $e, ?int $overwriteErrorCode = null)
    {
        if (!method_exists($e, 'getResponse')) {
            return new Response(json_encode([]),500,['Content-Type' => 'application/json']);
        }

        $response = $e->getResponse();
        $headers = $response->getHeaders();
        if (isset($headers['Content-Type'])) {
            $contentType = $headers['Content-Type'];
        } elseif (isset($headers['content-type'])) {
            $contentType = $headers['content-type'];
        } else {
            $contentType = 'application/json';
        }
        return new Response(
            (string)$response->getBody(),
            $overwriteErrorCode ?: $response->getStatusCode(),
            [
                'Content-Type' => $contentType
            ]
        );
    }
}
