# Version 1

### 1.2.2
- Chunk filter fixes
- Isset() CellCollection fixes
- PHP 5.3 support
- Missing border styles
- Add CSV settings (delimiter, enclosure, lineEnding) to ExcelFile objects

### 1.2.1
- Fix with() method parameters

### 1.2.0
- Filters
- Chunk filter (with chunked importer)
- ExcelFile (method) injections
- NewExcelFile (method) injections
- Edit existing worksheets
- Converting existing worksheet
- Laravel 4.* + 5.0 support

### 1.1.9
- PHP 5.3 fixes

### 1.1.8
- PHP 5.3 support
- fromArray bugfix

### 1.1.7
- Fix heading generation for export with `->fromArray()`
- Bugfix for non-Unix kernels
- Enhanced CSS parser (thanks to `tijsverkoyen/CssToInlineStyles`)
- Support for nested CSS styles
- Support for multiple css attributes per class
- Support for internal and external CSS files
- Support for inline style blocks (`<style>`)

### 1.1.6

- Provides.json fix
- DocBlock fixes
- Define Illuminate dependencies inside composer.json
- Better HTML rowspan handling views
- use new CellCollection() instead of ::make, to support upcoming Laravel version
- Workaround for long integers
- Add support to `wrap-text` in views
- Fix empty dates parsing
- Support local stylesheets in view parsing
- Push tr classes to td-children in views
- Support for dynamically appending rows to an empty (new) sheet
- Fix separator typo in config

### 1.1.5

- Select sheets by index with `Excel::selectSheetsByIndex(0,1)->load(...)`
- Separator typo fix
- Added `->setFileName()` method
- Use `->setTitle()` only for workbook title not for setting the filename anymore
- Made `setAutoSize()` chainable for other sheet methods
- Export config setting to disable pre calculation of formulas during export
- Export config setting to set the autosizing method (approx|exact)
- Auto sizing export from view fix

### 1.1.4

- Fix for importing 0 as null
- New unit tests

### 1.1.3

- Cell writer `->setBorder()` fix

### 1.1.2

- Fix for multiple imports on one pageload
- Multiple new import heading conversions (`Config: excel::import.heading: true|false|slugged|ascii|numeric|hashed|trans|original`)

### 1.1.1

- Retrieve workbook and sheet title during import (`->getTitle()`)

### 1.1.0

- `Limit()`, `skip()` and `take()` support for fetching results
- Set default page margins
- Export Eloquent models directly (`fromModel()`)
- Auto generate the first row (table heading) from the array keys
- Manipulate cells and cell ranges inside a closure
- Set cell backgrounds/fonts/values, ...
- Create/append/prepend new row/rows
- Manipulate row cells (background, fonts, ...)
- Config value default alignment on merge cells
- DocBlock updates to support better use of IDE autocomplete features
- Parse width and height inside views
- Parse images in views
- Optional to ASCII conversion of imported header columns (array indices)
- Config values for default null comparision and start cells for exports
- Changed default CSV enclosure to `"`
- Support for Laravel package installer

### 1.0.9

- Blade to Excel export fix for PHP5.3

### 1.0.8

- File format identifier enhancements

### 1.0.7

- Set workbook properties fix
- Extra units tests

### 1.0.6

- BatchReader fix

### 1.0.5

- Date parsing fix

### 1.0.4

- Fix calling $this in  anonymous function to set locale and cache

### 1.0.3

- Table headings to attribute names undefined offset fix
- Composer.json enhancements
- Documentation fixes

### 1.0.2

- Cell Collection fixes
- Default autosizing bugfixes
- ->load() accepts input encoding parameter
- Documentation fixes

### 1.0.1

- Column width and row height bugfix
- Typo fixes

### 1.0.0

- New documentation
- More logical file structure (dividing into files, separating the different functionality (import / export)
- More optional config settings
- CSV Delimiter fixes
- CSV Encoding
- Import into collections (to support utilisation of ->first(), etc.)
- Better column selecting and result limiting
- Batch upload
- Import dates as Carbon objects by default
- Advanced file import through config coordinates
- Select sheets to import
- Create closure (Excel::create('file', function($excel) { } ))
- More logical syntax for creating new files, syntaxes of creating by array and creating with view should be as identical as possible
- Rewrite of sheet building for views
- Using closures to build sheets for normal sheet creation
- Better support for calling native PHPExcel methods
- Better use of setters
- Config setting to set default store behavior
- Column/row width
- Share views over all sheets + easy views switching per sheet
- External stylesheet with classes/ids parsing for views
- Colspan fix
- Th default styling
- Caching / Cell caching