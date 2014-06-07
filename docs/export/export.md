# Exporting

To download the created file, use `->export($ext)` or `->download($ext)`.

#### Export to Excel5 (xls)

    Excel::create('Filename', function($excel) {

    })->export('xls');

    // or
    ->download('xls');

#### Export to Excel2007 (xlsx)

    ->export('xlsx');

    // or
    ->download('xlsx');

#### Export to CSV

    ->export('csv');

    // or
    ->download('csv');

> You can set the default enclosure and delimiter inside the config