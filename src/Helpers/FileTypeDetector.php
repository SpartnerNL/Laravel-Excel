<?php

namespace Maatwebsite\Excel\Helpers;

use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileTypeDetector
{
    /**
     * @throws NoTypeDetectedException
     */
    public static function detect(string|UploadedFile $filePath, ?string $type = null): ?string
    {
        if ($type !== null) {
            return $type;
        }

        if (!$filePath instanceof UploadedFile) {
            $pathInfo  = pathinfo($filePath);
            $extension = $pathInfo['extension'] ?? '';
        } else {
            $extension = $filePath->getClientOriginalExtension();
        }

        if (trim($extension) === '') {
            throw new NoTypeDetectedException;
        }

        return config('excel.extension_detector.' . strtolower($extension));
    }

    /**
     * @throws NoTypeDetectedException
     */
    public static function detectStrict(string $filePath, ?string $type = null): string
    {
        $type = static::detect($filePath, $type);

        if (!$type) {
            throw new NoTypeDetectedException;
        }

        return $type;
    }
}
