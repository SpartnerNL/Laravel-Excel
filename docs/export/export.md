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

#### Export to PDF

To export files to pdf, you will have to include `"dompdf/dompdf": "~0.6.1"`, `"mpdf/mpdf": "~5.7.3"` or `"tecnick.com/tcpdf": "~6.0.0"` in your `composer.json` and change the `export.pdf.driver` config setting accordingly.

    ->export('pdf');