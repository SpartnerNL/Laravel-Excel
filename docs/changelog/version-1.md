# Version 1

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