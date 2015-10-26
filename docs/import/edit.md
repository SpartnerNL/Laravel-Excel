# Editing existing files

You can edit existing Excel files, by loading them and after modification exporting them.

    Excel::load('file.csv', function($file) {

        // modify stuff

    })->export('csv');
