<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Fakes\ExcelFake;
use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelFakeTest extends TestCase
{
    /**
     * @test
     */
    public function can_fake_an_export()
    {
        ExcelFacade::fake();

        // Excel instance should be swapped to the fake now.
        $this->assertInstanceOf(ExcelFake::class, $this->app->make('excel'));
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_downloaded_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::download(new class {
        }, 'downloaded-filename.csv');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);

        ExcelFacade::assertDownloaded('downloaded-filename.csv', function ($export) {
            return $export instanceof Collection;
        });
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_stored_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::store(new class {
        }, 'stored-filename.csv', 's3');

        $this->assertTrue($response);

        ExcelFacade::assertStored('stored-filename.csv', 's3', function ($export) {
            return $export instanceof Collection;
        });
    }

    /**
     * @test
     */
    public function a_callback_can_be_passed_as_the_second_argument_when_asserting_against_a_faked_stored_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::store(new class {
        }, 'stored-filename.csv');

        $this->assertTrue($response);

        ExcelFacade::assertStored('stored-filename.csv', function ($export) {
            return $export instanceof Collection;
        });
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_queued_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::queue(new class {
        }, 'queued-filename.csv', 's3');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        ExcelFacade::assertQueued('queued-filename.csv', 's3', function ($export) {
            return $export instanceof Collection;
        });
    }

    /**
     * @test
     */
    public function a_callback_can_be_passed_as_the_second_argument_when_asserting_against_a_faked_queued_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::queue(new class {
        }, 'queued-filename.csv');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        ExcelFacade::assertQueued('queued-filename.csv', function ($export) {
            return $export instanceof Collection;
        });
    }
}
