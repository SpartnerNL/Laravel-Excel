<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Contracts\Filesystem\Filesystem as IlluminateFilesystem;

/**
 * @method string get(string $filename)
 * @method resource readStream(string $filename)
 * @method bool delete(string $filename)
 * @method bool exists(string $filename)
 */
class Disk
{
    /**
     * @param  array<string, mixed>  $diskOptions
     */
    public function __construct(
        protected IlluminateFilesystem $disk,
        protected ?string $name = null,
        protected array $diskOptions = [],
    ) {
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->disk->{$name}(...$arguments);
    }

    /**
     * @param  string|resource  $contents
     */
    public function put(string $destination, $contents): bool
    {
        return $this->disk->put($destination, $contents, $this->diskOptions);
    }

    public function copy(TemporaryFile $source, string $destination): bool
    {
        $readStream = $source->readStream();

        if (!is_resource($readStream)) {
            return false;
        }

        /** @var resource|closed-resource $readStream */
        $success = $this->put($destination, $readStream);

        if (is_resource($readStream)) {
            fclose($readStream);
        }

        return $success;
    }

    public function touch(string $filename): void
    {
        $this->disk->put($filename, '', $this->diskOptions);
    }
}
