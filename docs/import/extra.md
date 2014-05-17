# Extra

### Disable using first row as collection attributes

By default we will use the first row of a file as table heading (so as attribute names for the collection).
You can change the default behaviour inside `import.php` with `import.heading`.

To disable this for a single import, use `->noHeading()`.

    $reader->noHeading();

### Setting the cell name seperator
By default collection attribute names will be set by looking at the first row columns. Spaces will be translated to `_`.

**E.g. Created at -> created_at**

The default behaviour can be changed inside the `import.php` config by changing `'seperator'`. Or you can use `->setSeperator($seperator)`.

    $reader->setSeperator('-');

### Ignoring empty cells
By default empty cells will not be ignored and presented as null inside the cell collection.

To change the default behaviour, you can change `'ignoreEmpty`' inside `import.php` or use `->ignoreEmpty()`.

    $reader->ignoreEmpty();

### Input encoding

Inside the `import.php` config you can change the input encoding. In most cases **UTF-8** will be the best solution. Hower if you dump your results make sure your HTML page has this exact same meta charset!

### CSV Settings

Inside the `csv.php` config you can change the default settings, like the `delimiter`, the `enclosure` and the `line_ending`.