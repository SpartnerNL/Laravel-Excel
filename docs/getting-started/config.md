#Config

### Laravel 4

Laravel Excel includes several config settings for import-, export-, view- and CSV-specific settings.
Use the artisan publish command to publish the config file to your project.

    php artisan config:publish maatwebsite/excel

The config files can now be found at `app/config/packages/maatwebsite/excel`

### Laravel 5

To publish the config settings in Laravel 5 use:

    php artisan vendor:publish

This will add an `excel.php` config file to your config folder.
