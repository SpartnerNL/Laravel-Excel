# Importing a file

To start importing a file, you can use `->load($filename)`. The callback is optional.

    Excel::load('file.xls', function($reader) {

        // reader methods

    });
