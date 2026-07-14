<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Exceptions\NoFilenameGivenException;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;
use Maatwebsite\Excel\Exporter;
use Maatwebsite\Excel\Tests\Data\Stubs\EmptyExport;
use Maatwebsite\Excel\Tests\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportableTest extends TestCase
{
    public function test_needs_to_have_a_file_name_when_downloading(): void
    {
        $this->expectException(NoFilenameGivenException::class);
        $this->expectExceptionMessage('A filename needs to be passed in order to download the export');

        $export = new class implements Export
        {
            use Exportable;
        };

        $export->download();
    }

    public function test_needs_to_have_a_file_name_when_storing(): void
    {
        $this->expectException(NoFilePathGivenException::class);
        $this->expectExceptionMessage('A filepath needs to be passed in order to store the export');

        $export = new class implements Export
        {
            use Exportable;
        };

        $export->store();
    }

    public function test_needs_to_have_a_file_name_when_queuing(): void
    {
        $this->expectException(NoFilePathGivenException::class);
        $this->expectExceptionMessage('A filepath needs to be passed in order to store the export');

        $export = new class implements Export
        {
            use Exportable;
        };

        $export->queue();
    }

    public function test_responsable_needs_to_have_file_name_configured_inside_the_export(): void
    {
        $this->expectException(NoFilenameGivenException::class);
        $this->expectExceptionMessage('A filename needs to be passed in order to download the export');

        $export = new class implements Export, Responsable
        {
            use Exportable;
        };

        $export->toResponse(new Request);
    }

    public function test_is_responsable(): void
    {
        $export = new class implements Export, Responsable
        {
            use Exportable;

            public function __construct()
            {
                $this->fileName = 'export.xlsx';
            }
        };

        $this->assertInstanceOf(Responsable::class, $export);

        $response = $export->toResponse(new Request);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }

    public function test_can_have_customized_header(): void
    {
        $export = new class implements Export
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
        $this->assertSame('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_can_set_custom_headers_in_export_class(): void
    {
        $export = new class implements Export
        {
            use Exportable;

            public function __construct()
            {
                $this->fileName   = 'name.csv';
                $this->writerType = Excel::CSV;
                $this->headers    = ['Content-Type' => 'text/csv'];
            }
        };
        $response = $export->toResponse(request());

        $this->assertSame('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_can_get_raw_export_contents(): void
    {
        $export = new EmptyExport;

        $response = $export->raw(Excel::XLSX);

        $this->assertNotEmpty($response);
    }

    public function test_can_have_customized_disk_options_when_storing(): void
    {
        $export = new EmptyExport;

        $this->mock(Exporter::class)
            ->shouldReceive('store')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->store('name.csv', 's3', Excel::CSV, ['visibility' => 'private']);
    }

    public function test_can_have_customized_disk_options_when_queueing(): void
    {
        $export = new EmptyExport;

        $this->mock(Exporter::class)
            ->shouldReceive('queue')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->queue('name.csv', 's3', Excel::CSV, ['visibility' => 'private']);
    }

    public function test_can_set_disk_options_in_export_class_when_storing(): void
    {
        $export = new class implements Export
        {
            use Exportable;

            public function __construct()
            {
                $this->disk        = 's3';
                $this->writerType  = Excel::CSV;
                $this->diskOptions = ['visibility' => 'private'];
            }
        };

        $this->mock(Exporter::class)
            ->shouldReceive('store')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->store('name.csv');
    }

    public function test_can_set_disk_options_in_export_class_when_queuing(): void
    {
        $export = new class implements Export
        {
            use Exportable;

            public function __construct()
            {
                $this->disk        = 's3';
                $this->writerType  = Excel::CSV;
                $this->diskOptions = ['visibility' => 'private'];
            }
        };

        $this->mock(Exporter::class)
            ->shouldReceive('queue')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->queue('name.csv');
    }

    public function test_can_override_export_class_disk_options_when_calling_store(): void
    {
        $export = new class implements Export
        {
            use Exportable;

            public function __construct()
            {
                $this->diskOptions = ['visibility' => 'public'];
            }
        };

        $this->mock(Exporter::class)
            ->shouldReceive('store')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->store('name.csv', 's3', Excel::CSV, ['visibility' => 'private']);
    }

    public function test_can_override_export_class_disk_options_when_calling_queue(): void
    {
        $export = new class implements Export
        {
            use Exportable;

            public function __construct()
            {
                $this->diskOptions = ['visibility' => 'public'];
            }
        };

        $this->mock(Exporter::class)
            ->shouldReceive('queue')->once()
            ->with($export, 'name.csv', 's3', Excel::CSV, ['visibility' => 'private']);

        $export->queue('name.csv', 's3', Excel::CSV, ['visibility' => 'private']);
    }

    public function test_can_have_empty_disk_options_when_storing(): void
    {
        $export = new EmptyExport;

        $this->mock(Exporter::class)
            ->shouldReceive('store')->once()
            ->with($export, 'name.csv', null, null, []);

        $export->store('name.csv');
    }

    public function test_can_have_empty_disk_options_when_queueing(): void
    {
        $export = new EmptyExport;

        $this->mock(Exporter::class)
            ->shouldReceive('queue')->once()
            ->with($export, 'name.csv', null, null, []);

        $export->queue('name.csv');
    }
}
