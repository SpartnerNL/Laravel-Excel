---
name: laravel-excel
description: "Use when building, modifying, testing, documenting, or debugging Laravel spreadsheet exports and imports with maatwebsite/excel. Activate for every Laravel Excel feature: facade APIs, export/import classes, all concerns, queues, batches, multiple sheets, conditional sheets, headings, mapping, validation, failures, errors, chunking, batch inserts, upserts, CSV settings, value binders, read filters, formulas, charts, drawings, styles, events, properties, Scout exports, view exports, mapped cells, macros, Artisan generators, config/excel.php, temporary files, cache, transactions, cell middleware, Excel::fake(), PhpSpreadsheet integration, or package maintenance."
license: MIT
---

# Laravel Excel

Use this skill when a Laravel task involves spreadsheet exports, imports, queued spreadsheet processing, CSV files, or package maintenance for `maatwebsite/excel`.

## Read First

Use `search-docs` when it is available for Laravel framework integration details. For package-specific behavior in this repository, inspect:

- `src/Excel.php`
- `src/Exporter.php`
- `src/Importer.php`
- `src/Concerns/`
- `src/Fakes/ExcelFake.php`
- `config/excel.php`
- `tests/Concerns/`
- `tests/ExcelFakeTest.php`
- `../../examples/exports-imports.md`
- `../../references/package.md`

If the user asks for complete coverage, "all features", package docs, package skills, or a missing-feature audit, read `../../references/package.md` first and use it as the checklist.

## Working Rules

- Keep controllers thin. Put spreadsheet behavior in dedicated export/import classes.
- Use package concerns to declare behavior instead of custom imperative spreadsheet code.
- Before claiming feature completeness, compare the output against `../../references/package.md`.
- Use `Exportable` on export classes when the export should call `download()`, `store()`, `queue()`, or `raw()` directly.
- Use `Importable` on import classes when the import should call `import()`, `queue()`, `toArray()`, or `toCollection()` directly.
- Prefer `FromQuery` for large exports so the package can chunk records efficiently.
- Prefer `WithChunkReading` plus `WithBatchInserts` for large `ToModel` imports.
- Add Laravel's `Illuminate\Contracts\Queue\ShouldQueue` to exports/imports that may exceed request time limits.
- Use `WithMultipleSheets` for workbooks with multiple worksheets, and keep each sheet as its own export/import object.
- Use `WithHeadingRow` when import files have user-facing headers; validate using heading keys instead of numeric indexes.
- Use `WithValidation`, `SkipsOnFailure`, `SkipsFailures`, `SkipsOnError`, and `SkipsErrors` deliberately when importing untrusted user files.
- Use `WithCustomCsvSettings` for delimiter, enclosure, input encoding, or BOM-sensitive CSV work.
- Use PhpSpreadsheet concerns and events only when package concerns do not cover the required formatting or worksheet behavior.
- Do not load large spreadsheets fully into memory unless the task explicitly needs complete workbook inspection.

## Feature Coverage Checklist

Use `../../references/package.md` for the complete matrix. It covers:

- Facade APIs, object traits, contracts, file type constants, and extension detection.
- Export sources, export formatting, events, styles, formulas, charts, drawings, properties, multiple sheets, conditional sheets, and CSV settings.
- Import targets, heading rows, grouped headings, mapped cells, read filters, row/column limits, formulas, formatting, empty rows, unknown sheets, row numbers, and chunk offsets.
- Validation, failures, errors, batch inserts, upserts, duplicate handling, relation persistence, queues, batches, and queue-without-chain behavior.
- Console generators, published stubs/config, collection/query macros, cache, transactions, temporary files, remote temp disks, value binders, cell middleware, fakes, and package test locations.

## Common API

Exports:

- `Excel::download($export, 'file.xlsx')`
- `Excel::store($export, 'exports/file.xlsx', 'disk')`
- `Excel::queue($export, 'exports/file.xlsx', 'disk')`
- `Excel::raw($export, Excel::XLSX)`

Imports:

- `Excel::import($import, $filePath, $disk)`
- `Excel::queueImport($import, $filePath, $disk)`
- `Excel::toArray($import, $filePath, $disk)`
- `Excel::toCollection($import, $filePath, $disk)`

Writer and reader types are usually detected from the file extension. Pass explicit types such as `Excel::XLSX` or `Excel::CSV` when the extension is absent or ambiguous.

## Export Pattern

Start with the smallest concern set that matches the source:

- `FromCollection` for an existing collection.
- `FromArray` for simple arrays.
- `FromGenerator` or `FromIterator` for streamed in-memory rows.
- `FromQuery` for Eloquent or query builder datasets.
- `FromView` when a Blade table is the clearest representation.

Add output concerns only when needed:

- `WithHeadings` for header rows.
- `WithMapping` for row transformation.
- `WithColumnFormatting` for Excel number/date formats.
- `ShouldAutoSize` or `WithColumnWidths` for layout.
- `WithStyles`, `WithDefaultStyles`, `WithBackgroundColor`, or `WithEvents` for workbook styling.

## Import Pattern

Choose the import target deliberately:

- `ToModel` to persist rows as Eloquent models.
- `ToCollection` for application-level processing.
- `ToArray` for lightweight read-only extraction.
- `OnEachRow` for row-by-row custom handling.

For production user uploads, include validation and failure behavior instead of assuming perfect files. Use chunk reading for large files and avoid cross-row state that cannot survive queued chunk jobs.

## Queues And Batches

- If an export/import implements `ShouldQueue`, `store()` and `import()` may dispatch queued work.
- Use `queue()` or `queueImport()` when queueing should be explicit at the call site.
- Use `ShouldBatch` when the export/import should return a Laravel pending batch.
- Use `ShouldQueueWithoutChain` only when queued import chunks should not be chained.
- Keep queued export/import classes serializable. Avoid closures, open file handles, or non-serializable service instances on properties.

## Testing

- Use `Excel::fake()` when testing that a controller, command, job, or service requested the correct spreadsheet operation.
- Assert intent with `assertDownloaded()`, `assertStored()`, `assertQueued()`, `assertImported()`, and `assertExportedInRaw()`.
- Use callback assertions to verify the export/import object class and constructor state.
- Generate and read real spreadsheet files only when testing cell values, multiple sheets, formatting, formulas, or PhpSpreadsheet compatibility.
- In this package repository, follow existing PHPUnit tests under `tests/Concerns/`, `tests/ExcelFakeTest.php`, and `tests/Queued*Test.php`.

## Maintenance Notes

- Preserve Laravel auto-discovery entries in `composer.json` for the service provider and `Excel` facade alias.
- Keep config changes in `config/excel.php` backward compatible unless the task explicitly targets a breaking release.
- When changing a concern, update or add tests near the matching `tests/Concerns/*Test.php` file.
- When changing fakes, update `tests/ExcelFakeTest.php`.
- When changing queue behavior, update the matching queued import/export tests.
