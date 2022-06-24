<?php

namespace PajuranCodes\Router\Exception;

use function sprintf;

/**
 * An exception indicating that no route was found after matching 
 * the request components (the HTTP method and the URI path) to the 
 * components of a route (the list of HTTP methods and the pattern) 
 * in a collection of routes.
 * 
 * @link https://tools.ietf.org/html/rfc7231#section-6.5.4 6.5.4. 404 Not Found
 * @link https://tools.ietf.org/html/rfc7231#section-8.2 Status Code Registry
 * @link https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml Status Code Registry
 * 
 * @author pajurancodes
 */
class RouteNotFound extends \UnexpectedValueException {

    /**
     * 
     * @param string $httpMethod The HTTP method of a request.
     * @param string $uriPath The URI path of a request.
     */
    public function __construct(
        private readonly string $httpMethod,
        private readonly string $uriPath,
        string $message = '',
        int $code = 0,
        \Throwable $previous = null
    ) {
        if ($message === '') {
            $message = sprintf(
                'The requested resource could not be found at '
                . 'the location "%s", using the HTTP method "%s".',
                $this->uriPath,
                $this->httpMethod
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
     * Get the URI path of a request.
     * 
     * @return string
     */
    public function getUriPath(): string {
        return $this->uriPath;
    }

}
