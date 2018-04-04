# Mapping data

### Mapping rows

By adding `WithMapping` you map the data that needs to be added as row. This way you have control over the actual source for each column.
In case of using the Eloquent query builder: 

```php
class InvoicesExport implements FromQuery, WithMapping
    
    /**
    * @var Invoice $invoice
    */
    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            Date::dateTimeToExcel($invoice->created_at),
        ];
    }
}
```

### Adding a heading row

A heading row can easily be added by adding the `WithHeadings` concern. The heading row will be added
as very first row of the sheet.

```php
class InvoicesExport implements FromQuery, WithHeadings
    
    public function headings(): array
    {
        return [
            '#',
            'Date',
        ];
    }
}
```
