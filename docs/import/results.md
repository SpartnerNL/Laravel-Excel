# Handling imported results

### Getting all sheets and rows

After you have loaded a file, you can `->get()` the results like so:

    Excel::load('file.xls', function($reader) {

    })->get();

or

    Excel::load('file.xls', function($reader) {

        // Getting all results
        $results = $reader->get();

        // ->all() is a wrapper for ->get() and will work the same
        $results = $reader->all();

    });

> The `->get()` and `->all()` methods will return a sheet or row collection, depending on the amount of sheets the file has. You can disable this feature inside the `import.php` config by setting `'force_sheets_collection'` to `true`. When set to true it will always return a sheet collection.

### Collections

Sheets, rows and cells are collections, this means after doing a `->get()` you can use all default collection methods.

    // E.g. group the results
    $reader->get()->groupBy('firstname');

### Getting the first sheet or row

To get the first sheet or row, you can utilise `->first()`.

    $reader->first();

> **Note:** depending on the config `'force_sheets_collection'` it will return the first row or sheet.

### Limiting the results

When you only want to return the first x rows of a sheet, you can use `->take()` or `->limit()`.

    // You can either use ->take()
    $reader->take(10);

    // Or ->limit()
    $reader->limit(10);

### Result mutators

When you want to get an array instead of an object, you can use `->toArray()`.

    $reader->toArray();

When you want an object, you can alternativly (instead of get() or all()) use `->toObject()`.

    $reader->toObject();

### Displaying results

You can dump the results to a readable output by using `->dump()` or `->dd()`.

    // Dump the results
    $reader->dump();

    // Dump results and die
    $reader->dd();

### Iterating the results

You can iterate the results by using `->each()`.

    // Loop through all sheets
    $reader->each(function($sheet) {

        // Loop through all rows
        $sheet->each(function($row) {

        });

    });

> Alternatively you can also `foreach` the results.
