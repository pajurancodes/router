<?php

namespace PajuranCodes\Router;

/**
 * An interface to a URI generator.
 *
 * @author pajurancodes
 */
interface UriGeneratorInterface {

    /**
     * Generate a URI based on the provided route name.
     * 
     * This method finds a route by the given route name 
     * and returns the URI corresponding to its pattern.
     *
     * The URI is composed of the URI path - generated and injected with 
     * the values from the given list of route parameters - and the query 
     * string generated from the given list of query string arguments.
     *
     * The URI path is URL-encoded on-demand, according to 
     * RFC 3986, whereas the query string is always URL-encoded.
     *
     * @param string $routeName The name of a route.
     * @param (string|int|float|bool|null|object|array)[] $routeParameters (optional) A list of 
     * route parameters.
     * @param array $queryStringArguments (optional) A list of query string arguments.
     * @param string $uriFragment (optional) A URI fragment.
     * @param bool $encodeUriPath (optional) A flag to indicate if the generated URI path 
     * should be URL-encoded or not.
     * @param bool $encodeUriFragment (optional) A flag to indicate if the given URI fragment 
     * should be URL-encoded or not.
     * @return string The generated URI, structured as 
     * "URI path + '?' + query string + '#' + URI fragment".
     */
    public function generate(
        string $routeName,
        array $routeParameters = [],
        array $queryStringArguments = [],
        string $uriFragment = '',
        bool $encodeUriPath = false,
        bool $encodeUriFragment = true
    ): string;
}
