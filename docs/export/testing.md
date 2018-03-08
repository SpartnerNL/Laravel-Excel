# Testing

The Excel facade can be used to swap the exporter to a fake.

### Testing downloads

```php
/**
* @test
*/
public function user_can_download_invoices_export() 
{
    Excel::fake();

    $this->actingAs($this->givenUser())
         ->get('/invoices/download/xlsx');

    Excel::assertDownloaded('filename.xlsx');
}
```

### Testing storing exports

```php
/**
* @test
*/
public function user_can_store_invoices_export() 
{
    Excel::fake();

    $this->actingAs($this->givenUser())
         ->get('/invoices/store/xlsx');

    Excel::assertStored('filename.xlsx', 'diskName');
}
```

### Testing queuing exports

```php
/**
* @test
*/
public function user_can_queue_invoices_export() 
{
    Excel::fake();

    $this->actingAs($this->givenUser())
         ->get('/invoices/queue/xlsx');

    Excel::assertQueued('filename.xlsx', 'diskName');
}
```