# Mapping data

### Mapping rows

By adding `WithMapping` you map the data that needs to be added as row. 
In case of using the Eloquent query builder, 

```php
class InvoicesExport implements FromQuery, WithMapping
    
    public function map($row): array
    {
        return [
            $row->invoice_number,
            Date::dateTimeToExcel($invoice->created_at),
        ];
    }
}
```

### Adding a heading row

A heading row can easily be added by adding the `WithHeadings` concern.

```php
class InvoicesExport implements FromQuery, WithHeadings
    
    public function map($row): array
    {
        return [
            '#',
            'Date',
        ];
    }
}
```