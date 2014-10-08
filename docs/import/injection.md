# ExcelFile injections

Following the Laravel 5.0 philosophy with its new awesome FormRequest injections, we introduce you ExcelFile injections.

## ExcelFile class

This class is a wrapper for a file on your server. Inside the `getFile()` method you return the filename and it's location. Inside the `getFilters()` method you can enable filters, like the chunk filter.

    class UserListImport extends \Maatwebsite\Excel\Files\ExcelFile {

        public function getFile()
        {
            return storage_path('exports') . '/file.csv';
        }

        public function getFilters()
        {
            return [
                'chunk'
            ];
        }

    }

If you want to have the `getFile()` dynamic based on user's input, you can easily do:

    public function getFile()
    {
        // Import a user provided file
        $file = Input::file('report');
        $filename = $this->doSomethingLikeUpload($file);

        // Return it's location
        return $filename;
    }

## Usage

You can inject these ExcelFiles inside the __constructor or inside the method (when using Laravel 5.0), in e.g. the controller.

    class ExampleController extends Controller {

        public function importUserList(UserListImport $import)
        {
            // get the results
            $results = $import->get();
        }

    }

## CSV Settings

You can pass through optional CSV settings, like `$delimiter`, `$enclosure` and `$lineEnding` as protected properties of the class.

    class UserListImport extends \Maatwebsite\Excel\Files\ExcelFile {

        protected $delimiter  = ',';
        protected $enclosure  = '"';
        protected $lineEnding = '\r\n';

    }

## Import Handlers

To decouple your Excel-import code completely from the controller, you can use the import handlers.

    class ExampleController extends Controller {

        public function importUserList(UserListImport $import)
        {
            // Handle the import
            $import->handleImport();
        }

    }

The `handleImport()` method will dynamically call a handler class which is your class name appended with `Handler`

    class UserListImportHandler implements \Maatwebsite\Excel\Files\ImportHandler {

        public function handle(UserListImport $import)
        {
            // get the results
            $results = $import->get();
        }

    }
