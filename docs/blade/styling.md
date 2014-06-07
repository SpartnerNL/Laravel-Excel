# Styling sheets

### General styling

If you want to change the general styling of your sheet (not cell or range specific), you can use the `->setStyle()` method or any of the other setters which can be found inside the export documentation.

    // Font family
    $sheet->setFontFamily('Comic Sans MS');

    // Set font with ->setStyle()`
    $sheet->setStyle(array(
        'font' => array(
            'name'      =>  'Calibri',
            'size'      =>  12,
            'bold'      =>  true
        )
    ));

### Styling with PHPExcel methods

It's possible to style the sheets and specific cells with help of PHPExcel methods. This package includes a lot of shortcuts (see export documentation), but also always the use of the native methods.

    // Set background color for a specific cell
    $sheet->getStyle('A1')->applyFromArray(array(
        'fill' => array(
            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'FF0000')
        )
    ));

### Using HTML tags

Most of the HTML tags are supported.

    <html>

        <!-- Headings -->
        <td><h1>Big title</h1></td>

        <!--  Bold -->
        <td><b>Bold cell</b></td>
        <td><strong>Bold cell</strong></td>

        <!-- Italic -->
        <td><i>Italic cell</i></td>

        <!-- Images -->
        <td><img src="img.jpg" /></td>

    </html>

> Inside the `view.php` config you can change how these tags will be interpreted by Excel by default.

### Using HTML attributes

Some of the basic styling can be done with HTML attributes.

    <html>

        <!-- Horizontal alignment -->
        <td align="right">Big title</td>

        <!--  Vertical alignment -->
        <td valign="middle">Bold cell</td>

        <!-- Rowspan -->
        <td rowspan="3">Bold cell</td>

        <!-- Colspan -->
        <td colspan="6">Italic cell</td>

        <!-- Width -->
        <td width="100">Cell with width of 100</td>

        <!-- Height -->
        <td height="100">Cell with height of 100</td>

    </html>

### Styling through inline-styles

It's possible to use inline styles inside your view files. Most of the general styles are supported.

    <html>

        <!-- Cell with black background -->
        <td style="background-color: #000000;">Cell</td>

    </html>

> Inside the reference guide you can find a list of supported styles.

### Styling through external CSS file

**Basic** styling can be done through an external CSS file.
At this moment nested CSS is **not** supported yet. Only direct class and ID references will work.

External css file:

    #cell {
        background-color: #000000;
        color: #ffffff;
    }

    .cell {
        background-color: #000000;
        color: #ffffff;
    }

Table:

    <html>

        {{ HTML::style('css/table.css') }}

        <!-- Cell styled with class -->
        <td class="cell">Cell</td>

        <!-- Cell styled with ID -->
        <td id="cell">Cell</td>

    </html>

> Inside the reference guide you can find a list of supported styles.