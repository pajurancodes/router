<?php

namespace PajuranCodes\Router\Exception;

use function implode;
use function sprintf;

/**
 * An exception indicating that the HTTP 
 * method of a request is not supported.
 * 
 * @link https://tools.ietf.org/html/rfc7231#section-6.5.5 6.5.5. 405 Method Not Allowed
 * @link https://tools.ietf.org/html/rfc7231#section-7.4.1 7.4.1. Allow
 * @link https://tools.ietf.org/html/rfc7231#section-8.2 Status Code Registry
 * @link https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml Status Code Registry
 * 
 * @author pajurancodes
 */
class HttpMethodNotAllowed extends \UnexpectedValueException {

    /**
     * 
     * @param string $httpMethod The HTTP method of a request.
     * @param string[] $allowedMethods A list of supported HTTP methods.
     */
    public function __construct(
        private readonly string $httpMethod,
        private readonly array $allowedMethods,
        string $message = '',
        int $code = 0,
        \Throwable $previous = null
    ) {
        if ($message === '') {
            $message = sprintf(
                'The HTTP method "%s" of the current request is '
                . 'not supported. The allowed HTTP methods are: %s.',
                $this->httpMethod,
                implode(', ', $this->allowedMethods)
            );
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the HTTP method of a request.
     * 
     * @return string
     */
    public function getHttpMethod(): string {
        return $this->httpMethod;
    }

    /**
     * Get the list of supported HTTP methods.
     * 
     * @return string[]
     */
    public function getAllowedMethods(): array {
        return $this->allowedMethods;
    }

}
