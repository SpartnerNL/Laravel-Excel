# Chunk importer

When dealing with big files, it's better to import the data in big chunks. You can enable this with `filter('chunk')`;
To import it into chunks you can use `chunk($size, $callback)` instead of the normal `get()`. The first parameter is the size of the chunk. The second parameter is a closure which will return the results.

    Excel::filter('chunk')->load('file.csv')->chunk(250, function($results)
    {
            foreach($results as $row)
            {
                // do stuff
            }
    });

## ExcelFile class example:

When working with ExcelFile injections (in the constructor or as method injection), you can enable the chunk filter inside the ExcelFile class

    class UserListImport extends \Maatwebsite\Excel\Files\ExcelFile {

        public function getFile()
        {
            return 'file.csv';
        }

        public function getFilters()
        {
            return [
                'chunk'
            ];
        }

    }

Injected ExcelFile example:

    public function importUserList(UserListImport $import)
    {
        $import->chunk(250, function($results)
        {
            // do stuff
        })
    }
