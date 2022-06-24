<?php

namespace PajuranCodes\Router;

use function mkdir;
use function is_dir;
use function dirname;
use function is_array;
use function var_export;
use function is_writable;
use function file_exists;
use function file_put_contents;
use FastRoute\{
    RouteParser,
    DataGenerator,
};
use PajuranCodes\Router\RouteCollectionInterface;

/**
 * A route collector.
 *
 * @author pajurancodes
 */
class RouteCollector extends \FastRoute\RouteCollector {

    /**
     * The path to a cache file.
     *
     * @var string
     */
    private readonly string $cacheFile;

    /**
     * 
     * @param RouteCollectionInterface $routeCollection A collection of routes.
     * @param bool $cacheDisabled (optional) A flag to indicate if caching should be disabled or not.
     * @param string $cacheFile (optional) The path to a cache file.
     */
    public function __construct(
        RouteParser $routeParser,
        DataGenerator $dataGenerator,
        private readonly RouteCollectionInterface $routeCollection,
        private readonly bool $cacheDisabled = false,
        string $cacheFile = ''
    ) {
        parent::__construct($routeParser, $dataGenerator);

        $this->cacheFile = $this->buildCacheFile($cacheFile);

        if ($this->cacheDisabled) {
            $this->addRoutes();
        } else {
            if (!file_exists($this->cacheFile)) {
                $this->writeRouteDataToCacheFile();
            }
        }
    }

    /**
     * Build the path to a cache file.
     *
     * @param string $cacheFile The path to a cache file.
     * @return string The path to the cache file.
     * @throws \InvalidArgumentException The path to the cache file is not 
     * provided, even though caching is enabled.
     */
    private function buildCacheFile(string $cacheFile): string {
        if (!$this->cacheDisabled && empty($cacheFile)) {
            throw new \InvalidArgumentException(
                    'The caching mechanism of the router is enabled. '
                    . 'Therefore a cache file must be provided.'
            );
        }

        return $cacheFile;
    }

    /**
     * Add each route from the collection of routes.
     *
     * @return static
     */
    private function addRoutes(): static {
        foreach ($this->routeCollection as $route) {
            $this->addRoute(
                $route->getMethods(),
                $route->getPattern(),
                $route->getId()
            );
        }

        return $this;
    }

    /**
     * Get the route data provided by the data generator and write it to the cache file.
     * 
     * This happens only if the caching mechanism 
     * is enabled and the cache file does not exist.
     *
     * @return static
     * @throws \RuntimeException On any error during writing the route data to the cache file.
     */
    private function writeRouteDataToCacheFile(): static {
        $this
            ->createCacheDirectory()
            ->addRoutes()
        ;

        $routeData = parent::getData();

        $numberOfBytesWritten = file_put_contents(
            $this->cacheFile
            , '<?php return ' . var_export($routeData, true) . ';'
        );

        if ($numberOfBytesWritten === false) {
            throw new \RuntimeException(
                    'The route data could not be written to the '
                    . 'cache file "' . $this->cacheFile . '".'
            );
        }

        return $this;
    }

    /**
     * Create the cache directory, if it doesn't exist.
     * 
     * A new cache directory is created with the widest possible 
     * access, e.g. with the following rights (0777):
     * 
     *  - for owner: execute + write + read;
     *  - for owner's group: execute + write + read;
     *  - for everybody else: execute + write + read;
     *
     * The existing or the newly created directory must be writable.
     *
     * Nested directories are allowed.
     *
     * @link https://www.php.net/manual/en/function.chmod.php chmod
     *
     * @return static
     * @throws \RuntimeException The cache directory can not be created.
     * @throws \RuntimeException The cache directory is not writable.
     */
    private function createCacheDirectory(): static {
        $directory = dirname($this->cacheFile);

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true)) {
                throw new \RuntimeException(
                        'The parent directory of the cache file '
                        . '"' . $this->cacheFile . '" could not be created.'
                );
            }
        }

        if (!is_writable($directory)) {
            throw new \RuntimeException(
                    'The parent directory of the cache file '
                    . '"' . $this->cacheFile . '" must be writable.'
            );
        }

        return $this;
    }

    /**
     * Get the route data.
     * 
     * The data is either provided by the data generator,
     * or by reading the cache file - if it exists and 
     * the caching mechanism is enabled.
     *
     * @return array The collected route data.
     */
    public function getData(): array {
        if (!$this->cacheDisabled) {
            if (!file_exists($this->cacheFile)) {
                $this->writeRouteDataToCacheFile();
            }

            return $this->getRouteDataFromCacheFile();
        }

        return parent::getData();
    }

    /**
     * Read the route data from the cache file.
     *
     * @return array The collected route data.
     * @throws \RuntimeException The collected route data is not set or is not an array.
     */
    private function getRouteDataFromCacheFile(): array {
        $routeData = require $this->cacheFile;

        if (!is_array($routeData)) {
            throw new \RuntimeException(
                    'The route data collected from the cache file '
                    . '"' . $this->cacheFile . '" must be an array.'
            );
        }

        return $routeData;
    }

}
