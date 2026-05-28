---
name: laravel-excel
description: "Activate when the user works on spreadsheet imports or exports in Laravel with maatwebsite/excel. Use for Excel::download(), Excel::store(), Excel::raw(), Excel::import(), Excel::queue(), Excel::queueImport(), export and import classes, make:export, make:import, collection/query/view exports, ToModel imports, heading rows, mapping, validation, chunk reading, batch inserts, queued imports or exports, multiple sheets, column formatting, CSV settings, temporary file configuration, Excel::fake() assertions, or config/excel.php."
license: MIT
metadata:
  author: laravel
---

# Laravel Excel

Use this skill when a Laravel task involves `maatwebsite/excel` exports, imports, queueing, formatting, or package configuration.

## Documentation

Use `search-docs` first when it is available for Laravel-side integration patterns. For package-specific behavior, inspect:

- `src/Excel.php`
- `src/ExcelServiceProvider.php`
- `src/Concerns/*`
- `src/Console/ExportMakeCommand.php`
- `src/Console/ImportMakeCommand.php`
- `config/excel.php`
- `tests/*` for expected behavior and supported concern combinations
- `references/package.md` for a compact package reference covering concerns, macros, queueing, config, and test patterns

Load `references/package.md` when the task needs package-specific detail beyond the core workflow in this file.

## Package Surface

The package auto-discovers:

- Service provider: `Maatwebsite\Excel\ExcelServiceProvider`
- Facade alias: `Maatwebsite\Excel\Facades\Excel`

The service provider also registers:

- `make:export`
- `make:import`
- collection macros for download and store
- query builder macros for download, store, and import

## Installation And Setup

Install the package with Composer:

```bash
composer require maatwebsite/excel
```

Publish config when the app needs local overrides:

```bash
php artisan vendor:publish --provider="Maatwebsite\\Excel\\ExcelServiceProvider" --tag=config
```

Publish the generator stubs when the app wants to customize generated import or export classes:

```bash
php artisan vendor:publish --provider="Maatwebsite\\Excel\\ExcelServiceProvider" --tag=stubs
```

## Generate Classes

Prefer the package generators instead of hand-writing the initial class:

```bash
php artisan make:export UsersExport
php artisan make:export UsersExport --model=User
php artisan make:export UsersExport --model=User --query

php artisan make:import UsersImport
php artisan make:import UsersImport --model=User
```

Generated classes live in:

- `app/Exports`
- `app/Imports`

## Core Working Pattern

1. Start with `make:export` or `make:import`.
2. Pick the smallest concern set that matches the use case.
3. Keep data shaping inside the export or import class, not the controller.
4. Queue large workloads instead of processing them inline.
5. Test with `Excel::fake()` when the goal is verifying dispatch behavior, filenames, disks, and class wiring.

## Exports

Choose the export source concern based on the data shape:

- `FromCollection` for already materialized Laravel collections
- `FromArray` for small ad hoc arrays
- `FromQuery` for large Eloquent or query builder datasets
- `FromView` when a Blade table is the source of truth
- `FromGenerator` or `FromIterator` for streamed or memory-sensitive data

Common export concerns:

- `Exportable` for fluent `download()`, `store()`, and `queue()` on the export object
- `WithHeadings` to add header rows
- `WithMapping` to transform each row
- `WithColumnFormatting` for Excel number or date formats
- `ShouldAutoSize` or `WithColumnWidths` for column sizing
- `WithStyles`, `WithDefaultStyles`, and `WithBackgroundColor` for styling
- `WithProperties` for workbook metadata
- `WithMultipleSheets` and `WithTitle` for multi-sheet files
- `WithEvents` or `RegistersEventListeners` for workbook and sheet hooks
- `WithDrawings` or `WithCharts` when spreadsheet assets are required
- `WithStrictNullComparison` when empty strings must remain explicit cells
- `WithCustomCsvSettings` for CSV delimiter, BOM, encoding, and related output settings
- `WithCustomStartCell` or `WithMappedCells` for precise worksheet positioning

Typical export response:

```php
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

return Excel::download(new UsersExport(), 'users.xlsx');
```

Prefer `FromQuery` for large datasets. The package chunks query exports automatically using `config('excel.exports.chunk_size')`.

## Imports

Choose the import target concern based on how rows should be consumed:

- `ToModel` to turn rows into Eloquent models
- `ToCollection` for in-memory row processing
- `ToArray` for raw array access
- `OnEachRow` when each row needs imperative handling

Common import concerns:

- `Importable` for fluent `import()` and `queue()` on the import object
- `WithHeadingRow` or `WithGroupedHeadingRow` for heading-based row keys
- `WithValidation` for row validation rules
- `WithChunkReading` for large files
- `WithBatchInserts` for efficient `ToModel` imports
- `WithUpserts` and `WithUpsertColumns` for idempotent writes
- `WithStartRow` to skip preamble rows
- `WithReadFilter` or `WithLimit` to narrow input
- `SkipsEmptyRows`, `SkipsOnFailure`, `SkipsFailures`, `SkipsOnError`, and `SkipsErrors` for fault-tolerant imports
- `WithCalculatedFormulas` when formula values must be evaluated during import
- `WithCustomCsvSettings` for delimiter, escape character, and encoding overrides
- `WithCustomValueBinder` when cell type coercion must change
- `RemembersRowNumber` or `RemembersChunkOffset` when row-level diagnostics matter
- `SkipsUnknownSheets` and `WithMultipleSheets` for workbook imports with sheet selection

Typical import:

```php
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

Excel::import(new UsersImport(), $request->file('spreadsheet'));
```

For `ToModel` imports, keep `model(array $row)` focused on turning one row into one model instance. Use batch inserts and chunk reading for larger files.

## Queueing And Large Files

Large exports and imports should usually be queued.

Important package behavior:

- Queued imports must implement `ShouldQueue`
- Queued exports can be triggered explicitly with `Excel::queue()` or fluently via `Exportable`
- Some flows are implicitly queued depending on the export or import class behavior
- Query exports and chunk-reading imports already expose package-level chunk processing primitives

Typical queued import:

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UsersImport implements ToModel, WithChunkReading, ShouldQueue
{
    use Importable;

    public function model(array $row): User
    {
        return new User([
            'name' => $row[0],
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
```

Remote temporary file support is configured under `excel.temporary_files.*`. Use a shared remote disk for multi-worker queue setups.

## Configuration

Package configuration lives in `config/excel.php`. Important areas:

- `exports.chunk_size` for `FromQuery` export chunk size
- `exports.pre_calculate_formulas`
- `exports.strict_null_comparison`
- `exports.csv.*` for CSV output
- `imports.read_only`
- `imports.ignore_empty`
- `imports.heading_row.formatter`
- `imports.csv.*` for CSV input
- `imports.cells.middleware` for cell value preprocessing
- `extension_detector` for file type guessing
- `value_binder.default`
- `cache.driver` and `cache.batch.memory_limit`
- `temporary_files.*` for local and remote temp file handling
- `transactions.*` for import transaction behavior

If an import or export behaves unexpectedly, check config first before changing application code.

## Testing

Prefer `Excel::fake()` when verifying interaction with the package:

```php
use Maatwebsite\Excel\Facades\Excel;

Excel::fake();

Excel::download(new UsersExport(), 'users.xlsx');

Excel::assertDownloaded('users.xlsx');
```

The fake supports assertions for:

- `assertDownloaded()`
- `assertStored()`
- `assertQueued()`
- `assertQueuedWithChain()`
- `assertImported()`
- `assertExportedInRaw()`
- regex filename matching via `matchByRegex()`

Use full file assertions only when the spreadsheet contents themselves are the behavior under test.

## Query And Collection Macros

The service provider registers convenience macros:

- collection download and store helpers
- query builder `downloadExcel`
- query builder `storeExcel`
- query builder `import`
- query builder `importAs`

Prefer these only when they fit existing project conventions. For non-trivial workflows, dedicated import and export classes are usually clearer.

## Best Practices

- Prefer dedicated export and import classes over controllers filled with spreadsheet logic.
- Prefer `FromQuery` plus queueing for large exports.
- Pair `ToModel` imports with `WithBatchInserts` and `WithChunkReading` for large files.
- Use `WithHeadingRow` when the input file is business-managed and columns are named.
- Keep `WithMapping` and `model()` deterministic and side-effect light.
- Use `WithValidation` instead of ad hoc row guards when validation rules can express the constraint.
- Use `WithCustomCsvSettings` instead of hand-parsing CSV files.
- Keep sheet formatting concerns inside the export class, not post-processing controller code.

## Common Pitfalls

- Queuing an import that does not implement `ShouldQueue`
- Using `FromCollection` for very large datasets that should be query-driven
- Forgetting `WithHeadingRow` while treating row keys as headings
- Expecting empty strings to survive export without `WithStrictNullComparison`
- Missing a shared remote temp disk in distributed queued imports
- Hand-coding imports or exports instead of using concerns that already express the behavior
- Testing only the HTTP status while ignoring the generated file interaction
