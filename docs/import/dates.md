# Dates

By default the dates will be parsed as a **[Carbon object](https://github.com/briannesbitt/Carbon)**. You can disable date formatting completly inside `import.php` by setting `dates.enabled` to `false`.

To enable/disable date formatting for a single import, use `->formatDates($boolean, $format)`

    // Format the dates
    $reader->formatDates(true);

    // Disable date formatting
    $reader->formatDates(false);

    // Format dates + set date format
    $reader->formatDates(true, 'Y-m-d');

### Format dates

By default the dates are **not formatted**, but returned as a Carbon object. There are a couple of options to format them.

#### Formatting results after ->get()

Inside your loop you can utilise the Carbon method `->format($dateFormat)`

    $rows->each(function($row) {

        $created_at = $row->created_at->format('Y-m-d');

    });

#### Setting a default date format

Inside the config you can set a default date format. A Carbon object will no longer be returned.

Or you can use `->setDateFormat()`

    $reader->setDateFormat('Y-m-d');

### Setting custom date columns

Cells which are not Excel formatted dates will not be parsed as a date. To force this behaviour (or to use this with CSV imports), you can set these date columns manually: `->setDateColumns()`

    $reader->setDateColumns(array(
        'created_at',
        'deleted_at'
    ))->get();
