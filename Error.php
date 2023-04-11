<?php

namespace go1\util;

use Assert\LazyAssertionException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Error
{
    # Common HTTP error codes
    # #####################
    public const BAD_REQUEST                   = 400;
    public const UNAUTHORIZED                  = 401;
    public const PAYMENT_REQUIRED              = 402;
    public const FORBIDDEN                     = 403;
    public const NOT_FOUND                     = 404;
    public const METHOD_NOT_ALLOWED            = 405;
    public const NOT_ACCEPTABLE                = 406;
    public const PROXY_AUTHENTICATION_REQUIRED = 407;
    public const REQUEST_TIMEOUT               = 408;
    public const CONFLICT                      = 409;
    public const PRECONDITION_FAILED           = 412;
    public const PAYLOAD_TOO_LARGE             = 413;
    public const UPGRADE_REQUIRED              = 426;
    public const TOO_MANY_REQUESTS             = 429;
    public const HEADER_TOO_LARGE              = 431;
    public const SERVICE_UNAVAILABLE           = 503;
    public const GATEWAY_TIMEOUT               = 504;

    # Error inside services
    # #####################
    public const API_ERROR        = 1000;
    public const QUEUE_ERROR      = 2000;
    public const PORTAL_ERROR     = 3000;
    public const PORTAL_NO_LEGACY = 3001;
    public const USER_ERROR       = 4000;
    public const LO_ERROR         = 5000;
    public const LOB_ERROR        = 6000;
    public const ENROLMENT_ERROR  = 7000;
    public const OUTCOME_ERROR    = 8000;
    public const ENTITY_ERROR     = 9000;
    public const GRAPHIN_ERROR    = 10000;
    public const RULES_ERROR      = 11000;
    public const CLOUDINARY_ERROR = 12000;
    public const S3_ERROR         = 13000;
    public const FINDER_ERROR     = 14000;
    public const HISTORY_ERROR    = 15000;
    public const ONBOARD_ERROR    = 17000;

    # Credit service
    # ---------------------
    public const CREDIT_ERROR                         = 16000;
    public const CREDIT_NOT_FOUND                     = 16001;
    public const CREDIT_NOT_AVAILABLE                 = 16002;
    public const CREDIT_PRODUCT_UNMATCH               = 16003;
    public const CREDIT_INVALID_TRANSACTION_REFERENCE = 16004;
    public const CREDIT_CANNOT_UPDATE_PROPERTIES      = 16005;

    # Error outside services
    # #####################
    public const X_SERVICE_UNREACHABLE = 80000;
    public const ONBOARD_STRIPE_ERROR  = 17001;

    public static function throw(Exception $e)
    {
        throw $e;
    }

    public static function isBadServerResponse(int $code): bool
    {
        return ($code >= 500) && ($code <= 599);
    }

    public static function createMissingOrInvalidJWT(): JsonResponse
    {
        return new JsonResponse(['message' => 'Missing or invalid JWT.'], 403);
    }

    /**
    * Returns a simple error JSON response with a specified HTTP status code.
    *
    * @param mixed $e The error message or exception.
    * @param int $code The HTTP status code of the response. Defaults to 400 Bad Request if not specified.
    * @return JsonResponse The JSON response object.
    */
    public static function simpleErrorJsonResponse($e, $code = 400): JsonResponse
    {
        $isValidHttpStatus = array_key_exists($code, Response::$statusTexts);
        $code = $isValidHttpStatus ? $code : Response::HTTP_BAD_REQUEST;

        return new JsonResponse(['message' => $e instanceof Exception ? $e->getMessage() : $e], $code);
    }

    public static function jr($msg)
    {
        return static::simpleErrorJsonResponse($msg, 400);
    }

    public static function jr403($msg): JsonResponse
    {
        return static::simpleErrorJsonResponse($msg, 403);
    }

    public static function jr404($msg): JsonResponse
    {
        return static::simpleErrorJsonResponse($msg, 404);
    }

    public static function jr406($msg): JsonResponse
    {
        return static::simpleErrorJsonResponse($msg, 406);
    }

    public static function jr500($msg): JsonResponse
    {
        return static::simpleErrorJsonResponse($msg, 500);
    }

    public static function getLazyAssertionError(LazyAssertionException $e): array
    {
        $data = ['message' => $e->getMessage()];

        foreach ($e->getErrorExceptions() as $error) {
            $data['error'][] = [
                'path'    => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }

        return $data;
    }

    public static function createLazyAssertionJsonResponse(LazyAssertionException $e, int $httpCode = 400): JsonResponse
    {
        $data = static::getLazyAssertionError($e);
        return new JsonResponse($data, $httpCode);
    }

    /**
     * Format error message for consistence error message
     * @param array|null $errors
     * @return array
     */
    public static function formatError(?array $errors): array
    {
        if (empty($errors)) {
            return [];
        }
        $data = ['message' => $errors['message']];
        if (isset($errors['error_code'])) {
            $data['error_code'] = $errors['error_code'];
        }
        if (isset($errors['ref'])) {
            $data['ref'] = $errors['ref'];
        }
        if (!isset($errors['error'])) {
            return $data;
        }
        $additionalErrorTypes = ['path', 'error_code', 'ref', 'http_code'];
        foreach ($errors['error'] as $error) {
            $additionalError = ['message' => $error['message']];
            foreach ($additionalErrorTypes as $additionalErrorType) {
                if (isset($error[$additionalErrorType])) {
                    $additionalError[$additionalErrorType] = $error[$additionalErrorType];
                }
            }
            $data['additional_errors'][] = $additionalError;
        }
        return $data;
    }

    /**
     * Create json response for the error messages, This function can be used for the LazyAssertionException error and for error messages
     * @param array|null $errors
     * @param LazyAssertionException|null $e
     * @param int $httpCode
     * @return JsonResponse
     */
    public static function createMultipleErrorsJsonResponse(?array $errors, LazyAssertionException $e = null, int $httpCode = 400): JsonResponse
    {
        if ($e) {
            $errors = static::getLazyAssertionError($e);
        }
        return new JsonResponse(static::formatError($errors), $httpCode);
    }
}
