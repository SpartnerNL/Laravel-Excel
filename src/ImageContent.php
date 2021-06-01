<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class ImageContent
{
    protected BaseDrawing $drawing;
    protected string $content;
    protected string $extension;
    protected string $filename;

    protected function __construct(BaseDrawing $drawing, string $content, string $filename, string $extension)
    {
        $this->drawing   = $drawing;
        $this->content   = $content;
        $this->extension = $extension;
        $this->filename  = $filename;
    }

    /**
     * @param BaseDrawing|Drawing|MemoryDrawing $drawing
     *
     * @return static
     */
    public static function from($drawing): self
    {
        if ($drawing instanceof MemoryDrawing) {
            return static::fromMemory($drawing);
        }

        $zipReader     = fopen($drawing->getPath(), 'r');
        $imageContents = '';

        while (!feof($zipReader)) {
            $imageContents .= fread($zipReader, 1024);
        }

        fclose($zipReader);

        return new static(
            $drawing,
            $imageContents,
            $drawing->getFilename(),
            $drawing->getExtension()
        );
    }

    public static function fromMemory(MemoryDrawing $drawing): self
    {
        ob_start();

        call_user_func(
            $drawing->getRenderingFunction(),
            $drawing->getImageResource()
        );

        $imageContents = ob_get_contents();

        ob_end_clean();

        switch ($drawing->getMimeType()) {
            case MemoryDrawing::MIMETYPE_GIF:
                $extension = 'gif';
                break;
            case MemoryDrawing::MIMETYPE_JPEG :
                $extension = 'jpg';
                break;
            case MemoryDrawing::MIMETYPE_PNG :
            default:
                $extension = 'png';
                break;
        }

        return new static($drawing, $imageContents, Str::random() . '.' . $extension, $extension);
    }

    /**
     * @return Drawing|BaseDrawing|MemoryDrawing
     */
    public function drawing(): BaseDrawing
    {
        return $this->drawing;
    }

    /**
     * @param string      $path
     * @param string|null $disk
     * @param mixed       $options
     */
    public function store(string $path, ?string $disk = null, $options = []): void
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
