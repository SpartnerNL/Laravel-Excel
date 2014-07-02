# Selecting sheets and columns

### Selecting one specific sheet
If you want to select a single sheet, you can use `->selectSheets($name)`. Only that sheet will be loaded.

    Excel::selectSheets('sheet1')->load();

### Selecting multiple sheets
If you want to select multiple sheets inside your file, you can pass an array as the parameter;

    Excel::selectSheets('sheet1', 'sheet2')->load();

### Selecting sheets by index

    // First sheet
    Excel::selectSheetsByIndex(0)->load();

    // First and second sheet
    Excel::selectSheetsByIndex(0, 1)->load();

### Selecting columns

If you want to select only a couple of columns, you can use `->select($columns)` or pass an array as the first parameter of `->get($columns)`.

    // Select
    $reader->select(array('firstname', 'lastname'))->get();

    // Or
    $reader->get(array('firstname', 'lastname'));

> All get methods (like all(), first(), dump(), toArray(), ...) accept an array of columns.
