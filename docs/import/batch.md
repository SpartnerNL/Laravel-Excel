#Batch import

### Import a folder

To import an entire folder (only xls, xlsx and csv files will be imported), set the folder as the first parameter.

    Excel::batch('app/storage/uploads', function($rows, $file) {

        // Explain the reader how it should interpret each row,
        // for every file inside the batch
        $rows->each(function($row) {

            // Example: dump the firstname
            dd($row->firstname);

        });

    });

### Import multiple files

It's also possible to provide an array of files to import.

    $files = array(
        'file1.xls',
        'file2.xls'
    );

    Excel::batch($files, function($rows, $file) {

    });

### Import a folder and multiple sheets

When your files contain multiple sheets, you should also loop the sheets

    Excel::batch('app/storage/uploads', function($sheets, $file) {

        $sheets->each(function($sheet) {

        });

    });