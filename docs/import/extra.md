# Extra

### Disable using first row as collection attributes

By default we will use the first row of a file as table heading (so as attribute names for the collection).
You can change the default behaviour inside `import.php` with `import.heading`.

To disable this for a single import, use `->noHeading()`.

    $reader->noHeading();

### Setting the cell name separator
By default collection attribute names will be set by looking at the first row columns. Spaces will be translated to `_`.

**E.g. Created at -> created_at**

The default behaviour can be changed inside the `import.php` config by changing `'separator'`. Or you can use `->setSeparator($separator)`.

    $reader->setSeparator('-');

### Ignoring empty cells
By default empty cells will not be ignored and presented as null inside the cell collection.

To change the default behaviour, you can change `'ignoreEmpty`' inside `import.php` or use `->ignoreEmpty()`.

    $reader->ignoreEmpty();

### Input encoding

Inside the `import.php` config you can change the input encoding. In most cases **UTF-8** will be the best solution. Hower if you dump your results make sure your HTML page has this exact same meta charset!

Optionally you can pass the input encoding inside the `->load()` method.

    // When utilising a closure, you can pass the input encoding as third parameter.
    Excel::load('filename.csv', function($reader) {

    }, 'UTF-8');

    // or without a closure, you can use it as second parameter.
    Excel::load('filename.csv', 'UTF-8');

### CSV Settings

Inside the `csv.php` config you can change the default settings, like the `delimiter`, the `enclosure` and the `line_ending`.
