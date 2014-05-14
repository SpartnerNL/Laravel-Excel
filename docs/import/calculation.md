# Calculate formulas

By default formulas inside the file are being calculated an a result will be returned. Inside `import.php` config you can change the default behaviour.

If you want to enable/disable it per import, you can use `->calculate($boolean)`

    // Enable calculation
    $reader->calculate();

    // Disable calculation
    $reader->calculate(false);