# Queued

In case you are working with a lot of data, it might be wise to queue the entire process. 

Given we have the following export class:

```php
namespace App\Exports;

class InvoicesExport implements FromQuery
{
    use Exportable;

    public function query()
    {
        return Invoice::query();
    }
}
```

It's as easy as calling `->queue()` now.

```php
return (new InvoicesExport)->queue('invoices.xlsx');
```

### Appending jobs

The `queue()` method returns an instance of Laravel's `PendingDispatch`. This means you can chain extra jobs.

```php
return (new InvoicesExport)->queue('invoices.xlsx')->chain([
    new InvoiceExportCompletedJob(),
]);
```

```php
class InvoiceExportCompletedJob implements ShouldQueue
{
    use Queueable;

    public function handle()
    {
        // Do something.
    }
}
```

### Custom queues

Because `PendingDispatch` is returned, we can also change the queue that should be used.

```php
return (new InvoicesExport)->queue('invoices.xlsx')->allOnQueue('exports');
```