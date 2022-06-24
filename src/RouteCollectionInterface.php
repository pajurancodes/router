<?php

namespace PajuranCodes\Router;

use Closure;
use PajuranCodes\Router\RouteInterface;

/**
 * An interface to a collection of routes.
 *
 * @author pajurancodes
 */
interface RouteCollectionInterface extends \Countable, \IteratorAggregate {

    /**
     * Add a group.
     *
     * @param string $pattern A group pattern.
     * @param Closure $handler A group handler.
     * @return static
     */
    public function group(string $pattern, Closure $handler): static;

    /**
     * Add a route for one or more HTTP methods.
     *
     * @param string|string[] $methods One or more HTTP methods.
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    public function route(
        string|array $methods,
        string $pattern,
        string|array|object $handler
    ): RouteInterface;

    /**
     * Add a route for all HTTP methods.
     *
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    public function routeForAllMethods(string $pattern, string|array|object $handler): RouteInterface;

    /**
     * Add a route for the HTTP method GET.
     *
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    public function get(string $pattern, string|array|object $handler): RouteInterface;

    /**
     * Add a route for the HTTP method HEAD.
     *
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    public function head(string $pattern, string|array|object $handler): RouteInterface;

    /**
     * Add a route for the HTTP method POST.
     *
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    public function post(string $pattern, string|array|object $handler): RouteInterface;

    /**
     * Add a route for the HTTP method PUT.
     *
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    public function put(string $pattern, string|array|object $handler): RouteInterface;

    /**
     * Add a route for the HTTP method DELETE.
     *
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    public function delete(string $pattern, string|array|object $handler): RouteInterface;

    /**
     * Add a route for the HTTP method CONNECT.
     *
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    public function connect(string $pattern, string|array|object $handler): RouteInterface;

    /**
     * Add a route for the HTTP method OPTIONS.
     *
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    public function options(string $pattern, string|array|object $handler): RouteInterface;

    /**
     * Add a route for the HTTP method TRACE.
     *
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    public function trace(string $pattern, string|array|object $handler): RouteInterface;

    /**
     * Add a route for the HTTP method PATCH.
     *
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    public function patch(string $pattern, string|array|object $handler): RouteInterface;

    /**
     * Get a route by id.
     *
     * @param string $id A route id.
     * @return RouteInterface The found route.
     * @throws \UnexpectedValueException No route found.
     */
    public function getRouteById(string $id): RouteInterface;

    /**
     * Get a route by name.
     *
     * @param string $name A route name.
     * @return RouteInterface The found route.
     * @throws \UnexpectedValueException No route found.
     */
    public function getRouteByName(string $name): RouteInterface;

    /**
     * Check if a route exists in the collection.
     *
     * @param string $id A route id.
     * @return bool True if the specified route id exists, or false otherwise.
     */
    public function exists(string $id): bool;

    /**
     * Remove a route from the collection.
     *
     * @param string $id A route Id.
     * @return static
     */
    public function remove(string $id): static;

    /**
     * Get all routes from the collection.
     * 
     * @return RouteInterface[] All routes in the collection.
     */
    public function all(): array;

    /**
     * Remove all routes from the collection.
     * 
     * @return static
     */
    public function clear(): static;

    /**
     * Check if the collection is empty.
     * 
     * @return bool True if the collection is empty, or false otherwise.
     */
    public function isEmpty(): bool;

    /**
     * Count the routes in the collection.
     *
     * @return int Number of routes in the collection.
     */
    public function count(): int;

    /**
     * Get an iterator to iterate through the collection.
     *
     * @return \Traversable The routes iterator.
     */
    public function getIterator(): \Traversable;
}
