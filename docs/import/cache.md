# Caching and Cell caching

### Cell caching

You can enable cell caching inside the config `cache.php`. You can choose between a couple of drivers and change a couple of settings. By default the caching is **enabled** and will use **in memory** caching.

### Remembering results

If you want to remember the results you can use `->remember($minutes)`. Next time you will load the same file (if it's still inside the cache), it will return the cached results.

    // Remember for 10 minutes
    $results = $reader->remember(10)->get();