# Upgrade Guide

### Upgrading to 3.0 from 2.*

Version 3.0 will not be backwards compatible with 2.*. It's not possible to provide a migration guide.

#### New dependencies

* Requires PHP 7.0 or higher.
* Requires Laravel 5.5 (or higher).
* Requires PhpSpreadsheet instead of PHPExcel.

### Deprecations

ALL Laravel Excel 2.1 are deprecated and will not be able to use in 3.0 . 

- Excel::load() is removed and will not be re-added until 3.1
- Excel::create() is removed and replaced by Excel::download/Excel::store($yourExport);
