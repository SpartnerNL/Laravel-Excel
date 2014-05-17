# Auto size

By default the exported file be automatically auto sized. To change this behaviour you can either change the config or use the setters:

    // Set auto size for sheet
    $sheet->setAutoSize(true);

    // Disable auto size for sheet
    $sheet->setAutoSize(false);

    // Disable auto size for columns
    $sheet->setAutoSize(array(
        'A', 'C'
    ));

> The default config setting can be found in: `export.php`.