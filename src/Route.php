<?php

namespace PajuranCodes\Router;

use function is_string;
use function array_map;
use PajuranCodes\Router\RouteInterface;

/**
 * A route.
 *
 * @author pajurancodes
 */
class Route implements RouteInterface {

    /**
     * A list of HTTP methods.
     * 
     * @var string[]
     */
    private array $methods;

    /**
     * A list of parameters.
     * 
     * These are the values of the URI path corresponding
     * to the placeholders defined in the route pattern.
     * 
     * @var (string|int|float|bool|null|object|array)[]
     */
    private array $parameters = [];

    /**
     * An id.
     * 
     * @var string
     * 
     * @todo Instead of a string as route id, create a value object - named 'RouteId', for example.
     */
    private string $id = '';

    /**
     *
     * @param string|string[] $methods One or more HTTP methods.
     * @param string $pattern A pattern.
     * @param string|array|object $handler A handler.
     * @param string $name (optional) A name.
     */
    public function __construct(
        string|array $methods,
        private string $pattern,
        private string|array|object $handler,
        private string $name = ''
    ) {
        $this->setMethods($methods);
    }

    /**
     * @inheritDoc
     */
    public function getMethods(): array {
        return $this->methods;
    }

    /**
     * @inheritDoc
     * 
     * @todo Should the exception message be more precise, e.g. better identify the route?
     */
    public function setMethods(string|array $methods): static {
        if (is_string($methods)) {
            $methods = [$methods];
        }

        foreach ($methods as $key => $method) {
            if (!is_string($method)) {
                throw new \UnexpectedValueException(
                        'The value at the key "' . $key . '" of the '
                        . 'list of HTTP methods must be a string.'
                );
            }
        }

        $this->methods = array_map('strtoupper', $methods);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPattern(): string {
        return $this->pattern;
    }

    /**
     * @inheritDoc
     */
    public function setPattern(string $pattern): static {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHandler(): string|array|object {
        return $this->handler;
    }

    /**
     * @inheritDoc
     */
    public function setHandler(string|array|object $handler): static {
        $this->handler = $handler;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getParameters(): array {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function setParameters(array $parameters): static {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): static {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the id.
     *
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * Set the id.
     *
     * @param string $id An id.
     * @return static
     */
    public function setId(string $id): static {
        $this->id = $id;
        return $this;
    }

}
