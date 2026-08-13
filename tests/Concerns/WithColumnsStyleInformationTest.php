<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Columns\Column;
use Maatwebsite\Excel\Columns\Hyperlink;
use Maatwebsite\Excel\Columns\Image;
use Maatwebsite\Excel\Columns\RichText;
use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\ImageContent;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Columns that read hyperlinks, drawings or rich text need the sheet loaded with
 * style information. Every path that builds a reader has to work that out from the
 * column definitions alone, because the heading row isn't known yet.
 */
final class WithColumnsStyleInformationTest extends TestCase
{
    public function test_hyperlink_column_reads_urls_without_a_rich_text_sibling(): void
    {
        $import = new class implements ToArray, WithColumns
        {
            use Importable;

            /** @var array<int, array<string, mixed>> */
            public array $rows = [];

            public function array(array $array): void
            {
                $this->rows = $array;
            }

            /**
             * @return Hyperlink[]
             */
            public function columns(): array
            {
                return [
                    'M' => Hyperlink::make('link')->url(),
                ];
            }
        };

        $import->import('import-users-with-columns.xlsx');

        $this->assertSame('https://maatwebsite.com/', $import->rows[0]['link']);
        $this->assertSame('https://laravel.com/', $import->rows[1]['link']);
    }

    public function test_image_column_reads_drawings_without_a_rich_text_sibling(): void
    {
        $import = new class implements ToArray, WithColumns
        {
            use Importable;

            /** @var array<int, array<string, mixed>> */
            public array $rows = [];

            public function array(array $array): void
            {
                $this->rows = $array;
            }

            /**
             * @return Image[]
             */
            public function columns(): array
            {
                return [
                    'N' => Image::make('logo', fn (?ImageContent $image): ?string => $image?->filename()),
                ];
            }
        };

        $import->import('import-users-with-columns.xlsx');

        $this->assertSame('image1.jpg', $import->rows[0]['logo']);
    }

    public function test_rich_text_column_survives_a_heading_row(): void
    {
        $this->givenRichTextSheet('rich-text-with-headings.xlsx');

        $import = new class implements ToArray, WithColumns, WithHeadingRow
        {
            use Importable;

            /** @var array<int, array<string, mixed>> */
            public array $rows = [];

            public function array(array $array): void
            {
                $this->rows = $array;
            }

            /**
             * @return array<string, Column>
             */
            public function columns(): array
            {
                return [
                    'name' => Text::make('Name', 'name'),
                    'html' => RichText::make('Html', 'html'),
                ];
            }
        };

        $import->import('rich-text-with-headings.xlsx');

        // Under a heading row every column used to be replaced by an EmptyCell
        // before the reader was configured, so this came back as plain text.
        $this->assertStringContainsString('<span style=', (string) $import->rows[0]['html']);
        $this->assertStringContainsString('font-weight:bold', (string) $import->rows[0]['html']);
    }

    public function test_rich_text_column_survives_chunk_reading(): void
    {
        $this->givenRichTextSheet('rich-text-chunked.xlsx');

        $import = new class implements ToArray, WithChunkReading, WithColumns, WithHeadingRow
        {
            use Importable;

            /** @var array<int, array<string, mixed>> */
            public array $rows = [];

            public function array(array $array): void
            {
                $this->rows = array_merge($this->rows, $array);
            }

            public function chunkSize(): int
            {
                return 1;
            }

            /**
             * @return array<string, Column>
             */
            public function columns(): array
            {
                return [
                    'name' => Text::make('Name', 'name'),
                    'html' => RichText::make('Html', 'html'),
                ];
            }
        };

        $import->import('rich-text-chunked.xlsx');

        // ReadChunk used to re-derive read-only from config in the worker.
        $this->assertStringContainsString('<span style=', (string) $import->rows[0]['html']);
    }

    public function test_reading_rich_text_does_not_leak_into_later_imports(): void
    {
        $this->assertTrue(config('excel.imports.read_only'));

        $import = new class implements ToArray, WithColumns
        {
            use Importable;

            public function array(array $array): void
            {
                //
            }

            /**
             * @return RichText[]
             */
            public function columns(): array
            {
                return [
                    'J' => RichText::make('Html', 'html'),
                ];
            }
        };

        $import->import('import-users-with-columns.xlsx');

        // The decision belongs to the reader, not to global config.
        $this->assertTrue(config('excel.imports.read_only'));
    }

    /**
     * A sheet with a heading row whose second column holds rich text.
     */
    private function givenRichTextSheet(string $filename): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->getCell('A1')->setValue('name');
        $sheet->getCell('B1')->setValue('html');

        $sheet->getCell('A2')->setValue('Patrick Brouwers');
        $sheet->getCell('B2')->setValue((new Html)->toRichTextObject('<div>test <strong>bold</strong> test</div>'));

        IOFactory::createWriter($spreadsheet, 'Xlsx')
            ->save(__DIR__ . '/../Data/Disks/Local/' . $filename);
    }
}
