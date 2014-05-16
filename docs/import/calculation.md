# Calculate formulas

By default formulas inside the file are being calculated and it's result will be returned. Inside `import.php` config you can change the default behaviour by setting `calculate` to the desired preference.

If you want to enable/disable it for a single import, you can use `->calculate($boolean)`

    // Enable calculation
    $reader->calculate();

    // Disable calculation
    $reader->calculate(false);
