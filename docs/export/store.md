# Store on server

To store the created file on the server, use `->store($ext, $path = false, $returnInfo = false)` or `->save()`.

### Normal export to default storage path

By default the file will be stored inside the `app/storage/exports` folder, which has been defined in the `export.php` config file.

    Excel::create('Filename', function($excel) {

        // Set sheets

    })->store('xls');

### Normal export to custom storage path

If you want to use a custom storage path (e.g. to separate the files per client), you can set the folder as the second parameter.

    ->store('xls', storage_path('excel/exports'));

### Store and export

    ->store('xls')->export('xls');

### Store and return storage info

If you want to return storage information, set the third paramter to true or change the config setting inside `export.php`.

    ->store('xls', false, true);

|Key|Explanation|
|---|-----------|
|**full**| Full path with filename
|**path**| Path without filename
|**file**| Filename
|**title**| File title
|**ext**| File extension

> Make sure your storage folder is **writable**!