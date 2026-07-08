<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Factories\WriterFactory;
use Maatwebsite\Excel\Files\RemoteTemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFileFactory;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/** @mixin Spreadsheet */
class Writer
{
    use DelegatedMacroable, HasEventBus;

    protected ?Spreadsheet $spreadsheet = null;

    protected object $exportable;

    public function __construct(
        protected TemporaryFileFactory $temporaryFileFactory,
    ) {
        $this->setDefaultValueBinder();
    }

    /**
     * @throws Exception
     */
    public function export(object $export, string $writerType): TemporaryFile
    {
        $this->open($export);

        $sheetExports = [$export];
        if ($export instanceof WithMultipleSheets) {
            $sheetExports = $export->sheets();
        }

        foreach ($sheetExports as $sheetExport) {
            $this->addNewSheet()->export($sheetExport);
        }

        return $this->write($export, $this->temporaryFileFactory->makeLocal(null, strtolower($writerType)), $writerType);
    }

    public function open(object $export): static
    {
        $this->exportable = $export;

        if ($export instanceof WithEvents) {
            $this->registerListeners($export->registerEvents());
        }

        $this->exportable  = $export;
        $this->spreadsheet = new Spreadsheet;
        $this->spreadsheet->disconnectWorksheets();

        if ($export instanceof WithCustomValueBinder) {
            Cell::setValueBinder($export);
        }

        $this->handleDocumentProperties($export);

        if ($export instanceof WithBackgroundColor) {
            $defaultStyle    = $this->spreadsheet->getDefaultStyle();
            $backgroundColor = $export->backgroundColor();

            if (is_string($backgroundColor)) {
                $defaultStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($backgroundColor);
            }

            if (is_array($backgroundColor)) {
                $defaultStyle->applyFromArray(['fill' => $backgroundColor]);
            }

            if ($backgroundColor instanceof Color) {
                $defaultStyle->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor($backgroundColor);
            }
        }

        if ($export instanceof WithDefaultStyles) {
            $defaultStyle = $this->spreadsheet->getDefaultStyle();
            $styles       = $export->defaultStyles($defaultStyle);

            if (is_array($styles)) {
                $defaultStyle->applyFromArray($styles);
            }
        }

        $this->raise(new BeforeExport($this, $this->exportable));

        return $this;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function reopen(TemporaryFile $tempFile, string $writerType): Writer
    {
        $reader            = IOFactory::createReader($writerType);
        $this->spreadsheet = $reader->load($tempFile->sync()->getLocalPath());

        return $this;
    }

    /**
     * Determine if the application is running in a serverless environment.
     */
    public function isRunningServerless(): bool
    {
        return isset($_ENV['AWS_LAMBDA_RUNTIME_API']);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
     */
    public function write(object $export, TemporaryFile $temporaryFile, string $writerType): TemporaryFile
    {
        $this->exportable = $export;

        $this->spreadsheet->setActiveSheetIndex(0);

        $this->raise(new BeforeWriting($this, $this->exportable));

        $writer = WriterFactory::make(
            $writerType,
            $this->spreadsheet,
            $export,
            $temporaryFile->getLocalPath()
        );

        if ($temporaryFile instanceof RemoteTemporaryFile && !$temporaryFile->existsLocally() && !$this->isRunningServerless()) {
            // just ensure that local copy exists (it creates the directory structure),
            // no need to copy remote content since it will be overwritten below
            $temporaryFile->sync(false);
        }

        $writer->save(
            $temporaryFile->getLocalPath()
        );

        if ($temporaryFile instanceof RemoteTemporaryFile) {
            $temporaryFile->updateRemote();
            $temporaryFile->deleteLocalCopy();
        }

        $this->clearListeners();
        $this->spreadsheet->disconnectWorksheets();
        $this->spreadsheet = null;

        return $temporaryFile;
    }

    /**
     * @throws Exception
     */
    public function addNewSheet(?int $sheetIndex = null): Sheet
    {
        return new Sheet($this->spreadsheet->createSheet($sheetIndex));
    }

    public function getDelegate(): Spreadsheet
    {
        return $this->spreadsheet;
    }

    public function setDefaultValueBinder(): static
    {
        Cell::setValueBinder(
            app(config('excel.value_binder.default', DefaultValueBinder::class))
        );

        return $this;
    }

    /**
     * @throws Exception
     */
    public function getSheetByIndex(int $sheetIndex): Sheet
    {
        return new Sheet($this->getDelegate()->getSheet($sheetIndex));
    }

    public function hasConcern(string $concern): bool
    {
        return $this->exportable instanceof $concern;
    }

    protected function handleDocumentProperties(object $export): void
    {
        $properties = config('excel.exports.properties', []);

        if ($export instanceof WithProperties) {
            $properties = array_merge($properties, $export->properties());
        }

        if ($export instanceof WithTitle) {
            $properties = array_merge($properties, ['title' => $export->title()]);
        }

        $props = $this->spreadsheet->getProperties();

        foreach (array_filter($properties) as $property => $value) {
            switch ($property) {
                case 'title':
                    $props->setTitle($value);
                    break;
                case 'description':
                    $props->setDescription($value);
                    break;
                case 'creator':
                    $props->setCreator($value);
                    break;
                case 'lastModifiedBy':
                    $props->setLastModifiedBy($value);
                    break;
                case 'subject':
                    $props->setSubject($value);
                    break;
                case 'keywords':
                    $props->setKeywords($value);
                    break;
                case 'category':
                    $props->setCategory($value);
                    break;
                case 'manager':
                    $props->setManager($value);
                    break;
                case 'company':
                    $props->setCompany($value);
                    break;
            }
        }
    }
}
