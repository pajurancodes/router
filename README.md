# router

A request router based on the FastRoute library.

### Route caching on production

On instantiating the router, all routes from the route collection are added to the route collector,
independent of the cache settings. If caching is enabled, then the route data collected from the
route collector will also be written to the cache file. If the parent directory of the file
doesn't exist yet, then it will be created.

On request dispatching, if caching is enabled and the cache file exists, then the route data
collected from the cache file will be read. Otherwise the route data collected from the route
collector will be read.