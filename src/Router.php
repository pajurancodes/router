<?php

namespace PajuranCodes\Router;

use PajuranCodes\Router\{
    RouteInterface,
    RouterInterface,
    RouteCollectionInterface,
    Exception\RouteNotFound,
    Exception\HttpMethodNotAllowed,
};
use FastRoute\Dispatcher;

/**
 * A router.
 * 
 * This class matches the request components (the HTTP method and 
 * the URI path) to the components of a route (the list of HTTP 
 * methods and the pattern) in a collection of routes.
 *
 * @author pajurancodes
 */
class Router implements RouterInterface {

    /**
     *
     * @param RouteCollectionInterface $routeCollection A collection of routes.
     * @param Dispatcher $dispatcher A dispatcher.
     */
    public function __construct(
        private readonly RouteCollectionInterface $routeCollection,
        private readonly Dispatcher $dispatcher
    ) {
        
    }

    /**
     * @inheritDoc
     */
    public function match(string $httpMethod, string $uriPath): RouteInterface {
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uriPath);

        $statusCode = $routeInfo[0];

        $foundRoute = match ($statusCode) {
            Dispatcher::FOUND => $this->handleRouteFoundStatus(
                $routeInfo[1], // The route id.
                $routeInfo[2] // The list of route parameters.
            ),
            Dispatcher::NOT_FOUND => $this->handleRouteNotFoundStatus(
                $httpMethod,
                $uriPath
            ),
            Dispatcher::METHOD_NOT_ALLOWED => $this->handleHttpMethodNotAllowedStatus(
                $httpMethod,
                /*
                 * The list of allowed HTTP methods.
                 * 
                 * @see Route::methods
                 */
                $routeInfo[1]
            ),
            default => $this->handleUriDispatchingFailedStatus(),
        };

        return $foundRoute;
    }

    /**
     * Handle the situation indicating that a route was found.
     * 
     * @param string $routeId A route id.
     * @param (string|int|float|bool|null|object|array)[] $routeParameters A list of route parameters.
     * @return RouteInterface The route.
     */
    private function handleRouteFoundStatus(string $routeId, array $routeParameters): RouteInterface {
        $route = $this->routeCollection->getRouteById($routeId);

        $route->setParameters($routeParameters);

        return $route;
    }

    /**
     * Handle the situation indicating that a route was not found.
     * 
     * @link https://tools.ietf.org/html/rfc7231#section-6.5.4 6.5.4. 404 Not Found
     * 
     * @param string $httpMethod The HTTP method of a request.
     * @param string $uriPath The URI path of a request.
     * @return never
     * @throws RouteNotFound No route found.
     */
    private function handleRouteNotFoundStatus(string $httpMethod, string $uriPath): never {
        throw new RouteNotFound($httpMethod, $uriPath);
    }

    /**
     * Handle the situation indicating that the 
     * HTTP method of a request is not supported.
     * 
     * Note: The HTTP specification requires that a "405 Method Not Allowed" 
     * response include the "Allow:" header to detail available methods for 
     * the requested resource. Applications using FastRoute should use the 
     * list of supported HTTP methods to add this header when relaying a 
     * 405 response.
     * 
     * @link https://tools.ietf.org/html/rfc7231#section-6.5.5 6.5.5. 405 Method Not Allowed
     * @link https://tools.ietf.org/html/rfc7231#section-7.4.1 7.4.1. Allow
     * 
     * @param string $httpMethod The HTTP method of a request.
     * @param string[] $allowedMethods A list of supported HTTP methods.
     * @return never
     * @throws HttpMethodNotAllowed The HTTP method of the request is not supported.
     */
    private function handleHttpMethodNotAllowedStatus(string $httpMethod, array $allowedMethods): never {
        throw new HttpMethodNotAllowed($httpMethod, $allowedMethods);
    }

    /**
     * Handle the situation indicating 
     * that the URI dispatching failed.
     * 
     * @return never
     * @throws \RuntimeException Failure during the URI dispatching.
     */
    private function handleUriDispatchingFailedStatus(): never {
        throw new \RuntimeException('An error occurred during the process of URI dispatching.');
    }

}
