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

Behind the scenes the query will be chunked and multiple jobs will be chained. These jobs will be executed in the correct order,
and will only execute if none of the previous have failed. 

### Implicit Export queueing

You can also mark an export implicitly as a queued export. You can do this by using Laravel's `ShouldQueue` contract.

```php
namespace App\Exports;

class InvoicesExport implements FromQuery, ShouldQueue
{
    use Exportable;

    public function query()
    {
        return Invoice::query();
    }
}
```

In your controller you can now call the normal `->store()` method. 
Based on the presence of the `ShouldQueue` contract, the export will be queued.

```php
return (new InvoicesExport)->store('invoices.xlsx');
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

### Chaining jobs

It's also possible to chain extra jobs, that will be added to the end of the queue and only 
executed if all export jobs are correctly executed.

```php
return (new InvoicesExport)
    ->queue('invoices.xlsx')
    ->chain([
        new NotifyUserOfCompletedExport(request()->user())
    ]);
```