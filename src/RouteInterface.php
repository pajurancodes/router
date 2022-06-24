<?php

namespace PajuranCodes\Router;

/**
 * An interface to a route.
 *
 * @author pajurancodes
 */
interface RouteInterface {

    /**
     * Get the list of HTTP methods.
     *
     * @return string[]
     */
    public function getMethods(): array;

    /**
     * Set the list of HTTP methods.
     * 
     * Each value is made uppercase before saving.
     *
     * @param string|string[] $methods One or more HTTP methods.
     * @return static
     * @throws \UnexpectedValueException One of the provided HTTP methods is not a string.
     */
    public function setMethods(string|array $methods): static;

    /**
     * Get the pattern.
     *
     * @return string
     */
    public function getPattern(): string;

    /**
     * Set the pattern.
     *
     * @param string $pattern A pattern.
     * @return static
     */
    public function setPattern(string $pattern): static;

    /**
     * Get the handler.
     *
     * @return string|array|object
     */
    public function getHandler(): string|array|object;

    /**
     * Set the handler.
     *
     * @param string|array|object $handler A handler.
     * @return static
     */
    public function setHandler(string|array|object $handler): static;

    /**
     * Get the list of parameters.
     *
     * @return (string|int|float|bool|null|object|array)[]
     */
    public function getParameters(): array;

    /**
     * Set the list of parameters.
     *
     * @param (string|int|float|bool|null|object|array)[] $parameters A list of parameters.
     * @return static
     */
    public function setParameters(array $parameters): static;

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set the name.
     *
     * @param string $name A name.
     * @return static
     */
    public function setName(string $name): static;
}
