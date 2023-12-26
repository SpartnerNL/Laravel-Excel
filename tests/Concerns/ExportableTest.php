<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Exporter;
use Maatwebsite\Excel\Tests\Data\Stubs\EmptyExport;
use Maatwebsite\Excel\Tests\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportableTest extends TestCase
{
    /**
     * @test
     */
    public function needs_to_have_a_file_name_when_downloading()
    {
        $this->expectException(\Maatwebsite\Excel\Exceptions\NoFilenameGivenException::class);
        $this->expectExceptionMessage('A filename needs to be passed in order to download the export');

        $export = new class
        {
            use Exportable;
        };

        $export->download();
    }

    /**
     * @test
     */
    public function needs_to_have_a_file_name_when_storing()
    {
        $this->expectException(\Maatwebsite\Excel\Exceptions\NoFilePathGivenException::class);
        $this->expectExceptionMessage('A filepath needs to be passed in order to store the export');

        $export = new class
        {
            use Exportable;
        };

        $export->store();
    }

    /**
     * @test
     */
    public function needs_to_have_a_file_name_when_queuing()
    {
        $this->expectException(\Maatwebsite\Excel\Exceptions\NoFilePathGivenException::class);
        $this->expectExceptionMessage('A filepath needs to be passed in order to store the export');

        $export = new class
        {
            use Exportable;
        };

        $export->queue();
    }

    /**
     * @test
     */
    public function responsable_needs_to_have_file_name_configured_inside_the_export()
    {
        $this->expectException(\Maatwebsite\Excel\Exceptions\NoFilenameGivenException::class);
        $this->expectExceptionMessage('A filename needs to be passed in order to download the export');

        $export = new class implements Responsable
        {
            use Exportable;
        };

        $export->toResponse(new Request());
    }

    /**
     * @test
     */
    public function is_responsable()
    {
        $export = new class implements Responsable
        {
            use Exportable;

            protected $fileName = 'export.xlsx';
        };

        $this->assertInstanceOf(Responsable::class, $export);

        $response = $export->toResponse(new Request());

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }

    /**
     * @test
     */
    public function can_have_customized_header()
    {
        $export   = new class
        {
            use Exportable;
        };
        $response = $export->download(
            'name.csv',
            Excel::CSV,
            [
                'Content-Type' => 'text/csv',
            ]
        );
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
    }

    /**
     * @test
     */
    public function can_set_custom_headers_in_export_class()
    {
        $export   = new class
        {
            use Exportable;

            protected $fileName   = 'name.csv';
            protected $writerType = Excel::CSV;
            protected $headers    = [
                'Content-Type' => 'text/csv',
            ];
        };
        $response = $export->toResponse(request());

        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
    }

    /**
     * @test
     */
    public function can_get_raw_export_contents()
    {
        $export = new EmptyExport;

        $response = $export->raw(Excel::XLSX);

        $this->assertNotEmpty($response);
    }

    /**
     * @test
     */
    public function can_have_customized_disk_options_when_storing()
    {
        $export = new EmptyExport;

        $this->mock(Exporter::class)
            ->shouldReceive('store')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->store('name.csv', 's3', Excel::CSV, ['visibility' => 'private']);
    }

    /**
     * @test
     */
    public function can_have_customized_disk_options_when_queueing()
    {
        $export = new EmptyExport;

        $this->mock(Exporter::class)
            ->shouldReceive('queue')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->queue('name.csv', 's3', Excel::CSV, ['visibility' => 'private']);
    }

    /**
     * @test
     */
    public function can_set_disk_options_in_export_class_when_storing()
    {
        $export = new class
        {
            use Exportable;

            public $disk        = 's3';
            public $writerType  = Excel::CSV;
            public $diskOptions = ['visibility' => 'private'];
        };

        $this->mock(Exporter::class)
            ->shouldReceive('store')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->store('name.csv');
    }

    /**
     * @test
     */
    public function can_set_disk_options_in_export_class_when_queuing()
    {
        $export = new class
        {
            use Exportable;

            public $disk        = 's3';
            public $writerType  = Excel::CSV;
            public $diskOptions = ['visibility' => 'private'];
        };

        $this->mock(Exporter::class)
            ->shouldReceive('queue')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->queue('name.csv');
    }

    /**
     * @test
     */
    public function can_override_export_class_disk_options_when_calling_store()
    {
        $export = new class
        {
            use Exportable;

            public $diskOptions = ['visibility' => 'public'];
        };

        $this->mock(Exporter::class)
            ->shouldReceive('store')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->store('name.csv', 's3', Excel::CSV, ['visibility' => 'private']);
    }

    /**
     * @test
     */
    public function can_override_export_class_disk_options_when_calling_queue()
    {
        $export = new class
        {
            use Exportable;

            public $diskOptions = ['visibility' => 'public'];
        };

        $this->mock(Exporter::class)
            ->shouldReceive('queue')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->queue('name.csv', 's3', Excel::CSV, ['visibility' => 'private']);
    }

    /**
     * @test
     */
    public function can_have_empty_disk_options_when_storing()
    {
        $export = new EmptyExport;

        $this->mock(Exporter::class)
            ->shouldReceive('store')->once()
            ->with($export, 'name.csv', null, null, []);

        $export->store('name.csv');
    }

    /**
     * @test
     */
    public function can_have_empty_disk_options_when_queueing()
    {
        $export = new EmptyExport;

        $this->mock(Exporter::class)
            ->shouldReceive('queue')->once()
            ->with($export, 'name.csv', null, null, []);

        $export->queue('name.csv');
    }
}
