<?php

declare(strict_types=1);

namespace Maatwebsite\Excel;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use RuntimeException;

class ImageContent
{
    protected function __construct(
        protected BaseDrawing $drawing,
        protected string $content,
        protected string $filename,
        protected string $extension,
    ) {
    }

    public static function from(BaseDrawing $drawing): self
    {
        if ($drawing instanceof MemoryDrawing) {
            return static::fromMemory($drawing);
        }

        if (!$drawing instanceof Drawing) {
            throw new RuntimeException('Unable to read the contents of a ' . $drawing::class . '.');
        }

        $contents = file_get_contents($drawing->getPath());

        if ($contents === false) {
            throw new RuntimeException('Unable to read the image at ' . $drawing->getPath() . '.');
        }

        return new self(
            $drawing,
            $contents,
            $drawing->getFilename(),
            $drawing->getExtension()
        );
    }

    public static function fromMemory(MemoryDrawing $drawing): self
    {
        ob_start();

        ($drawing->getRenderingFunction())($drawing->getImageResource());

        $contents = ob_get_clean();

        if ($contents === false) {
            throw new RuntimeException('Unable to render the in-memory image.');
        }

        $extension = match ($drawing->getMimeType()) {
            MemoryDrawing::MIMETYPE_GIF  => 'gif',
            MemoryDrawing::MIMETYPE_JPEG => 'jpg',
            default                      => 'png',
        };

        return new self($drawing, $contents, Str::random() . '.' . $extension, $extension);
    }

    public function drawing(): BaseDrawing
    {
        return $this->drawing;
    }

    /**
     * @param  array<string, mixed>|string  $options
     */
    public function store(string $path, ?string $disk = null, array|string $options = []): void
    {
        Storage::disk($disk)->put($path, $this->content, $options);
    }

    public function content(): string
    {
        return $this->content;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function filename(): string
    {
        return $this->filename;
    }
}
