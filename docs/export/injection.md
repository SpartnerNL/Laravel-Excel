# NewExcelFile injections

Following the Laravel 5.0 philosophy with its new awesome FormRequest injections, we introduce you NewExcelFile injections.

## NewExcelFile class

This NewExcelFile is a wrapper for a new Excel file. Inside the `getFilename()` you can declare the wanted filename.

    class UserListExport extends \Maatwebsite\Excel\Files\NewExcelFile {

        public function getFilename()
        {
            return 'filename';
        }
    }

## Usage

You can inject these NewExcelFiles inside the __constructor or inside the method (when using Laravel 5.0), in e.g. the controller.

    class ExampleController extends Controller {

        public function exportUserList(UserListExport $export)
        {
            // work on the export
            return $export->sheet('sheetName', function($sheet)
            {

            })->export('xls');
        }

    }

## Export Handlers

To decouple your Excel-export code completely from the controller, you can use the export handlers.

    class ExampleController extends Controller {

        public function exportUserList(UserListExport $export)
        {
            // Handle the export
            $export->handleExport();
        }

    }

The `handleExport()` method will dynamically call a handler class which is your class name appended with `Handler`

    class UserListExportHandler implements \Maatwebsite\Excel\Files\ExportHandler {

        public function handle(UserListExport $export)
        {
            // work on the export
            return $export->sheet('sheetName', function($sheet)
            {

            })->export('xls');
        }

    }
