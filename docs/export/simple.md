# Simple Excel Export

### Basics

A new file can be created using the `create` method with the filename as first parameter.

    Excel::create('Filename');

To manipulate the creation of the file you can use the callback

    Excel::create('Filename', function($excel) {

        // Call writer methods here

    });

### Changing properties

There are a couple of properties we can change inside the closure. Most of them are set to the config values by default. See `app/config/packages/maatwebsite/excel/config.php`.

    Excel::create('Filename', function($excel) {

        // Set the title
        $excel->setTitle('Our new awesome title');

        // Chain the setters
        $excel->setCreator('Maatwebsite')
              ->setCompany('Maatwebsite');

        // Call them separately
        $excel->setDescription('A demonstration to change the file properties');

    });

> Go to the reference guide to see a list of available properties.