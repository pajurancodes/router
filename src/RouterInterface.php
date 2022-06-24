<?php

namespace PajuranCodes\Router;

use PajuranCodes\Router\RouteInterface;

/**
 * An interface to a router.
 *
 * @author pajurancodes
 */
interface RouterInterface {

    /**
     * Match the request components (the HTTP method and the URI path) to 
     * the components of each route (the list of HTTP methods and the pattern) in a
     * collection of routes and return the matched route.
     * 
     * @link https://github.com/nikic/FastRoute#dispatching-a-uri FastRoute: Dispatching a URI
     * 
     * @param string $httpMethod The HTTP method of a request.
     * @param string $uriPath The URI path of a request.
     * @return RouteInterface The matched route, if found.
     */
    public function match(string $httpMethod, string $uriPath): RouteInterface;
}
