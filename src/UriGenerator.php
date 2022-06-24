<?php

namespace PajuranCodes\Router;

use const PHP_QUERY_RFC3986;
use function count;
use function implode;
use function is_array;
use function is_string;
use function rawurlencode;
use function array_reverse;
use function array_key_exists;
use function http_build_query;
use PajuranCodes\Router\{
    RouteInterface,
    UriGeneratorInterface,
    RouteCollectionInterface,
};
use FastRoute\RouteParser;

/**
 * A URI generator.
 * 
 * This class generates a URI based on a provided route name.
 *
 * @author pajurancodes
 */
class UriGenerator implements UriGeneratorInterface {

    /**
     *
     * @param RouteCollectionInterface $routeCollection A collection of routes.
     * @param RouteParser $routeParser A route parser.
     */
    public function __construct(
        private readonly RouteCollectionInterface $routeCollection,
        private readonly RouteParser $routeParser
    ) {
        
    }

    /**
     * @inheritDoc
     */
    public function generate(
        string $routeName,
        array $routeParameters = [],
        array $queryStringArguments = [],
        string $uriFragment = '',
        bool $encodeUriPath = false,
        bool $encodeUriFragment = true
    ): string {
        $route = $this->getRouteByName($routeName);

        $routePattern = $route->getPattern();

        $listOfRouteDataArrays = $this->parseRoutePattern($routePattern);

        $uriPath = $this->buildUriPath($listOfRouteDataArrays, $routeParameters);

        if ($encodeUriPath) {
            $uriPath = $this->percentEncodeUriPath($uriPath);
        }

        $queryString = $this->generatePercentEncodedQueryString($queryStringArguments);

        if ($encodeUriFragment) {
            $uriFragment = $this->percentEncodeUriFragment($uriFragment);
        }

        $uri = $uriPath;

        if (!empty($queryString)) {
            $uri .= '?' . $queryString;
        }

        if (!empty($uriFragment)) {
            $uri .= '#' . $uriFragment;
        }

        return $uri;
    }

    /**
     * Get a route by name.
     *
     * @param string $routeName The name of a route.
     * @return RouteInterface The found route.
     */
    private function getRouteByName(string $routeName): RouteInterface {
        return $this->routeCollection->getRouteByName($routeName);
    }

    /**
     * Parse the pattern string of a route into multiple route data arrays.
     *
     * For example, the route 
     * 
     * "/uri-generator/test/{name}/abc/def[ghi/{id:\d+}/jkl/{age:\d+}[mno/{salary:[-+]?[0-9]*\.?[0-9]+}pqr]]"
     * 
     * will be parsed into the following list of route data arrays (three, in total):
     *
     *  [
     *      [
     *          "/uri-generator/test/",
     *          [
     *              "name",
     *              "[^/]+"
     *          ],
     *          "/abc/def"
     *      ],
     *      [
     *          "/uri-generator/test/",
     *          [
     *              "name",
     *              "[^/]+"
     *          ],
     *          "/abc/defghi/",
     *          [
     *              "id",
     *              "\d+"
     *          ],
     *          "/jkl/",
     *          [
     *              "age",
     *              "\d+"
     *          ]
     *      ],
     *      [
     *          "/uri-generator/test/",
     *          [
     *              "name",
     *              "[^/]+"
     *          ],
     *          "/abc/defghi/",
     *          [
     *              "id",
     *              "\d+"
     *          ],
     *          "/jkl/",
     *          [
     *              "age",
     *              "\d+"
     *          ],
     *          "mno/",
     *          [
     *              "salary",
     *              "[-+]?[0-9]*\.?[0-9]+"
     *          ],
     *          "pqr"
     *      ]
     *  ]
     *
     * @see FastRoute\RouteParser
     * @link https://github.com/nikic/FastRoute#overriding-the-route-parser-and-dispatcher Overriding the route parser and dispatcher (2nd paragraph).
     *
     * @param string $routePattern The pattern string of a route.
     * @return array[] The list of route data arrays.
     */
    private function parseRoutePattern(string $routePattern): array {
        return $this->routeParser->parse($routePattern);
    }

    /**
     * Build a URI path from the given list of route data arrays.
     *
     * @param array[] $listOfRouteDataArrays A list of route data arrays.
     * @param (string|int|float|bool|null|object|array)[] $routeParameters A list of route parameters.
     * @return string The URI path.
     * @throws \UnexpectedValueException One of the items in a route data array is not a string or an array.
     * @throws \UnexpectedValueException No route parameter matches the parameters defined 
     * in the route pattern.
     */
    private function buildUriPath(array $listOfRouteDataArrays, array $routeParameters): string {
        $uriPath = '';
        $routeParametersMatched = false;

        /*
         * Reverse the list of route data arrays, so that the 
         * last element, e.g. the element containing all route 
         * parameters, comes on the first place.
         */
        $reversedListOfRouteDataArrays = $this->reverseListOfRouteDataArrays($listOfRouteDataArrays);

        // Iterate through the reversed list of route data arrays.
        foreach ($reversedListOfRouteDataArrays as $routeDataArray) {
            $uriPathParts = [];

            // Iterate through the current route data array.
            foreach ($routeDataArray as $routeDataValue) {
                if (is_string($routeDataValue)) {
                    // If the value is a string, append it to the URI path.
                    $uriPathParts[] = $routeDataValue;
                } elseif (is_array($routeDataValue)) {
                    /*
                     * If the value is an array, then it 
                     * contains a parameter name and its regex.
                     */
                    $parameterName = $routeDataValue[0];

                    /*
                     * If the parameter name doesn't correspond to any key in the 
                     * given list of route parameters, or the value of an existing 
                     * route parameter is not set or is an empty string, then 
                     * continue the loop with the next route data array, since it 
                     * makes no sense anymore to read the next parameter names from 
                     * the current route data array.
                     */
                    if (
                        !array_key_exists($parameterName, $routeParameters) ||
                        !isset($routeParameters[$parameterName]) ||
                        $routeParameters[$parameterName] === ''
                    ) {
                        continue(2);
                    }

                    // Add the value of the found route parameter to the URI path.
                    $uriPathParts[] = $routeParameters[$parameterName];
                } else {
                    throw new \UnexpectedValueException(
                            'Every item of a route data array must be either a string or an array.'
                    );
                }
            }

            $uriPath = implode('', $uriPathParts);
            $routeParametersMatched = true;
            break;
        }

        if (!$routeParametersMatched) {
            throw new \UnexpectedValueException(
                    'The provided route parameters do not match '
                    . 'the ones defined in the route pattern.'
            );
        }

        return $uriPath;
    }

    /**
     * Reverse a list of route data arrays.
     * 
     * Upon reversing, the last element, e.g. the element 
     * containing all route parameters, comes on the first place.
     *
     * @param array[] $listOfRouteDataArrays A list of route data arrays.
     * @return array[] The inverted list of route data arrays.
     */
    private function reverseListOfRouteDataArrays(array $listOfRouteDataArrays): array {
        return count($listOfRouteDataArrays) > 1 ?
            array_reverse($listOfRouteDataArrays) :
            $listOfRouteDataArrays;
    }

    /**
     * URL-encode a URI path according to RFC 3986.
     * 
     * @link https://tools.ietf.org/html/rfc3986 Uniform Resource Identifier (URI): Generic Syntax
     *
     * @param string $uriPath A URI path.
     * @return string The URL-encoded URI path.
     */
    private function percentEncodeUriPath(string $uriPath): string {
        return rawurlencode($uriPath);
    }

    /**
     * Generate a URL-encoded query string from a list of query string arguments.
     * 
     * The encoding is performed according to RFC 3986.
     * 
     * @link https://tools.ietf.org/html/rfc3986 Uniform Resource Identifier (URI): Generic Syntax
     *
     * @param array $queryStringArguments A list of query string arguments.
     * @return string The generated URL-encoded query string.
     */
    private function generatePercentEncodedQueryString(array $queryStringArguments): string {
        return http_build_query($queryStringArguments, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * URL-encode the given URI fragment.
     * 
     * The encoding is performed according to RFC 3986.
     * 
     * @link https://tools.ietf.org/html/rfc3986 Uniform Resource Identifier (URI): Generic Syntax
     *
     * @param string $uriFragment A URI fragment.
     * @return string The URL-encoded URI fragment.
     */
    private function percentEncodeUriFragment(string $uriFragment): string {
        return rawurlencode($uriFragment);
    }

}
