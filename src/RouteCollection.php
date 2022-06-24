<?php

namespace PajuranCodes\Router;

use function count;
use function implode;
use function array_pop;
use function array_key_exists;
use PajuranCodes\Router\{
    Route,
    RouteInterface,
    RouteCollectionInterface,
};
use Closure;
use Fig\Http\Message\RequestMethodInterface as RequestMethod;

/**
 * A collection of routes.
 *
 * @author pajurancodes
 */
class RouteCollection implements RouteCollectionInterface {

    /**
     * A string to be concatenated to a counter in order to form a route id.
     * 
     * @var string
     */
    private const ROUTE_ID_PREFIX = 'route';

    /**
     * A list of allowed HTTP methods.
     * 
     * @link https://tools.ietf.org/html/rfc7231#section-4 4. Request Methods
     * @link https://www.iana.org/assignments/http-methods/http-methods.xhtml Hypertext Transfer Protocol (HTTP) Method Registry
     * @link https://tools.ietf.org/html/rfc5789 PATCH Method for HTTP
     * 
     * @var string[]
     */
    private const ROUTE_ALLOWED_METHODS = [
        RequestMethod::METHOD_GET,
        RequestMethod::METHOD_HEAD,
        RequestMethod::METHOD_POST,
        RequestMethod::METHOD_PUT,
        RequestMethod::METHOD_DELETE,
        RequestMethod::METHOD_CONNECT,
        RequestMethod::METHOD_OPTIONS,
        RequestMethod::METHOD_TRACE,
        RequestMethod::METHOD_PATCH,
    ];

    /**
     * A list of routes as an associative array.
     * 
     * The key of each list item is a route id 
     * built from a prefix string and a counter.
     * 
     * @var RouteInterface[]
     */
    private array $routes = [];

    /**
     * A list of group patterns as an indexed array.
     *
     * {@internal Each time a group handler is executed, the group 
     * pattern is pushed to this list. When a route is added to the 
     * routes list (inside the scope of a group handler), the route 
     * pattern is prefixed with the string formed by concatenating 
     * all group patterns saved in this list.}
     *
     * @var string[]
     */
    private array $groupPatterns = [];

    /**
     * A counter used to be prefixed with a given string in order to form a route id.
     * 
     * {@internal After a route is added to the 
     * routes list this counter is incremented.}
     *
     * @var int
     */
    private int $routeIdCounter = 0;

    /**
     * @inheritDoc
     */
    public function group(string $pattern, Closure $handler): static {
        $this->addGroup($pattern, $handler);
        return $this;
    }

    /**
     * Add a group.
     *
     * @param string $pattern A group pattern.
     * @param Closure $handler A group handler.
     * @return static
     */
    private function addGroup(string $pattern, Closure $handler): static {
        $this->saveGroupPattern($pattern);
        $this->executeGroupHandler($handler);

        /*
         * Remove the last group pattern from the group patterns list. 
         * This step is performed only after all calls for adding 
         * groups/routes inside the scope of the current group 
         * handler have finished their processing.
         */
        $this->popLastGroupPattern();

        return $this;
    }

    /**
     * Save a group pattern.
     *
     * @param string $pattern A group pattern.
     * @return static
     */
    private function saveGroupPattern(string $pattern): static {
        $this->groupPatterns[] = $pattern;
        return $this;
    }

    /**
     * Execute a group handler.
     *
     * {@internal This method temporarily binds the given group handler to
     * the instance of the route collection - defined by the argument of 
     * Closure::call - and executes it. Inside the scope of the group handler, 
     * the route collection will be accessed using the keyword "$this".}
     * 
     * @link https://www.php.net/manual/en/closure.call.php Closure::call
     *
     * @param Closure $handler A group handler.
     * @return static The value returned by executing the group handler.
     * 
     * @todo If needed, how to pass arguments to the group handler, e.g. to the callable?
     */
    private function executeGroupHandler(Closure $handler): static {
        $handler->call($this);
        return $this;
    }

    /**
     * Pop and return the last group pattern in the list of group patterns.
     * 
     * The list will be shortened by one element.
     * 
     * @return string|null The last group pattern, or null if the list is empty.
     */
    private function popLastGroupPattern(): ?string {
        return array_pop($this->groupPatterns);
    }

    /**
     * @inheritDoc
     */
    public function route(
        string|array $methods,
        string $pattern,
        string|array|object $handler
    ): RouteInterface {
        return $this->addRoute($methods, $pattern, $handler);
    }

    /**
     * @inheritDoc
     */
    public function routeForAllMethods(string $pattern, string|array|object $handler): RouteInterface {
        return $this->addRoute(self::ROUTE_ALLOWED_METHODS, $pattern, $handler);
    }

    /**
     * @inheritDoc
     */
    public function get(string $pattern, string|array|object $handler): RouteInterface {
        return $this->addRoute(RequestMethod::METHOD_GET, $pattern, $handler);
    }

    /**
     * @inheritDoc
     */
    public function head(string $pattern, string|array|object $handler): RouteInterface {
        return $this->addRoute(RequestMethod::METHOD_HEAD, $pattern, $handler);
    }

    /**
     * @inheritDoc
     */
    public function post(string $pattern, string|array|object $handler): RouteInterface {
        return $this->addRoute(RequestMethod::METHOD_POST, $pattern, $handler);
    }

    /**
     * @inheritDoc
     */
    public function put(string $pattern, string|array|object $handler): RouteInterface {
        return $this->addRoute(RequestMethod::METHOD_PUT, $pattern, $handler);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $pattern, string|array|object $handler): RouteInterface {
        return $this->addRoute(RequestMethod::METHOD_DELETE, $pattern, $handler);
    }

    /**
     * @inheritDoc
     */
    public function connect(string $pattern, string|array|object $handler): RouteInterface {
        return $this->addRoute(RequestMethod::METHOD_CONNECT, $pattern, $handler);
    }

    /**
     * @inheritDoc
     */
    public function options(string $pattern, string|array|object $handler): RouteInterface {
        return $this->addRoute(RequestMethod::METHOD_OPTIONS, $pattern, $handler);
    }

    /**
     * @inheritDoc
     */
    public function trace(string $pattern, string|array|object $handler): RouteInterface {
        return $this->addRoute(RequestMethod::METHOD_TRACE, $pattern, $handler);
    }

    /**
     * @inheritDoc
     */
    public function patch(string $pattern, string|array|object $handler): RouteInterface {
        return $this->addRoute(RequestMethod::METHOD_PATCH, $pattern, $handler);
    }

    /**
     * Add a route.
     *
     * @param string|string[] $methods One or more HTTP methods.
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    private function addRoute(
        string|array $methods,
        string $pattern,
        string|array|object $handler
    ): RouteInterface {
        $this
            ->validateRouteMethods($methods)
            ->validateRouteHandler($handler)
        ;

        $prefixedPattern = $this->prefixRoutePatternWithGroupPatterns($pattern);

        $route = $this->createRoute($methods, $prefixedPattern, $handler);

        return $this->saveRoute($route);
    }

    /**
     * Validate a list of route methods.
     *
     * @param string|string[] $methods One or more HTTP methods.
     * @return static
     * @throws \InvalidArgumentException The list of HTTP methods is empty.
     */
    private function validateRouteMethods(string|array $methods): static {
        if (empty($methods)) {
            throw new \InvalidArgumentException('One or more HTTP methods must be provided.');
        }

        return $this;
    }

    /**
     * Validate a route handler.
     *
     * @param string|array|object $handler A route handler.
     * @return static
     * @throws \InvalidArgumentException An empty route handler.
     */
    private function validateRouteHandler(string|array|object $handler): static {
        if (empty($handler)) {
            throw new \InvalidArgumentException('The route handler can not be empty.');
        }

        return $this;
    }

    /**
     * Prefix a route pattern with the pattern formed by 
     * concatenating the list of group patterns in a single string.
     *
     * @param string $pattern A route pattern.
     * @return string The prefixed route pattern.
     */
    private function prefixRoutePatternWithGroupPatterns(string $pattern): string {
        return $this->getConcatenatedGroupPatterns() . $pattern;
    }

    /**
     * Get the pattern formed by concatenating the 
     * list of group patterns in a single string.
     *
     * @return string The resulted pattern.
     */
    private function getConcatenatedGroupPatterns(): string {
        return $this->groupPatterns ? implode('', $this->groupPatterns) : '';
    }

    /**
     * Create a route.
     *
     * @param string|string[] $methods One or more HTTP methods.
     * @param string $pattern A route pattern.
     * @param string|array|object $handler A route handler.
     * @return RouteInterface The route.
     */
    private function createRoute(
        string|array $methods,
        string $pattern,
        string|array|object $handler
    ): RouteInterface {
        return new Route($methods, $pattern, $handler);
    }

    /**
     * Save a route.
     * 
     * Before saving, an id is assigned to the route.
     *
     * @param RouteInterface $route A route.
     * @return RouteInterface The route.
     */
    private function saveRoute(RouteInterface $route): RouteInterface {
        $id = $this->buildRouteId();

        $route->setId($id);

        $this->routes[$id] = $route;

        $this->routeIdCounter++;

        return $route;
    }

    /**
     * Build a route id.
     * 
     * The id is built by concatenating the 
     * route id prefix and the route id counter.
     *
     * @return string The route id.
     */
    private function buildRouteId(): string {
        return self::ROUTE_ID_PREFIX . (string) $this->routeIdCounter;
    }

    /**
     * @inheritDoc
     */
    public function getRouteById(string $id): RouteInterface {
        if (!$this->exists($id)) {
            throw new \UnexpectedValueException(
                    'A route with the id "' . $id . '" could not be found.'
            );
        }

        return $this->routes[$id];
    }

    /**
     * @inheritDoc
     */
    public function getRouteByName(string $name): RouteInterface {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }

        throw new \UnexpectedValueException(
                'A route with the name "' . $name . '" could not be found.'
        );
    }

    /**
     * @inheritDoc
     */
    public function exists(string $id): bool {
        return array_key_exists($id, $this->routes);
    }

    /**
     * @inheritDoc
     */
    public function remove(string $id): static {
        if ($this->exists($id)) {
            unset($this->routes[$id]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function all(): array {
        return $this->routes;
    }

    /**
     * @inheritDoc
     */
    public function clear(): static {
        $this->routes = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool {
        return !($this->count() > 0);
    }

    /**
     * @inheritDoc
     */
    public function count(): int {
        return count($this->routes);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Traversable {
        return new \ArrayIterator($this->routes);
    }

}
