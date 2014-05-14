# Importing a file

To start importing a file, you will have to `->load($filename)` it. The callback is optional.

    Excel::load('file.xls', function($reader) {

        // reader methods

    });