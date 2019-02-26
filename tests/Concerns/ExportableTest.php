<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;
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

        $export = new class {
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

        $export = new class {
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

        $export = new class {
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

        $export = new class implements Responsable {
            use Exportable;
        };

        $export->toResponse(new Request());
    }

    /**
     * @test
     */
    public function is_responsable()
    {
        $export = new class implements Responsable {
            use Exportable;

            protected $fileName = 'export.xlsx';
        };

        $this->assertInstanceOf(Responsable::class, $export);

        $response = $export->toResponse(new Request());

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }
}
