# Testing

The Excel facade can be used to swap the exporter to a fake.

```php
/**
* @test
*/
public function user_can_download_invoices_export() 
{
    Excel::fake();

    $this->actingAs($this->givenUser())
         ->get('/invoices/export');

    Excel::assertDownloaded('filename.xlsx');
}
```