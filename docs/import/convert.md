# Converting

You can convert from one filetype to another by using `->convert()`

    Excel::load('file.csv', function($file) {

        // modify stuff

    })->convert('xls');