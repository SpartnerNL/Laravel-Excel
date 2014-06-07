# Sheet styling

### General styling

If you want to change the general styling of your sheet (not cell or range specific), you can use the `->setStyle()` method.

    // Set font with ->setStyle()`
    $sheet->setStyle(array(
        'font' => array(
            'name'      =>  'Calibri',
            'size'      =>  15,
            'bold'      =>  true
        )
    ));

### Fonts

To change the font for the current sheet use `->setFont($array)`:

    $sheet->setFont(array(
        'family'     => 'Calibri',
        'size'       => '15',
        'bold'       => true
    ));

#### Separate setters

    // Font family
    $sheet->setFontFamily('Comic Sans MS');

    // Font size
    $sheet->setFontSize(15);

    // Font bold
    $sheet->setFontBold(true);

### Borders

You can set borders for the sheet, by using:

    // Sets all borders
    $sheet->setAllBorders('thin');

    // Set border for cells
    $sheet->setBorder('A1', 'thin');

    // Set border for range
    $sheet->setBorder('A1:F10', 'thin');

> Go to the reference guide to see a list of available border styles