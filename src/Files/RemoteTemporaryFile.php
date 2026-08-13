<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Support\Arr;
use Override;

class RemoteTemporaryFile extends TemporaryFile
{
    private ?Disk $diskInstance = null;

    public function __construct(
        private readonly string $disk,
        private readonly string $filename,
        private LocalTemporaryFile $localTemporaryFile,
    ) {
        $this->disk()->touch($this->filename);
    }

    /**
     * @return list<string>
     */
    public function __sleep(): array
    {
        return ['disk', 'filename', 'localTemporaryFile'];
    }

    public function getLocalPath(): string
    {
        return $this->localTemporaryFile->getLocalPath();
    }

    public function existsLocally(): bool
    {
        return $this->localTemporaryFile->exists();
    }

    public function exists(): bool
    {
        return $this->disk()->exists($this->filename);
    }

    public function deleteLocalCopy(): bool
    {
        return $this->localTemporaryFile->delete();
    }

    public function delete(): bool
    {
        // we don't need to delete local copy as it's deleted at end of each chunk
        if (!config('excel.temporary_files.force_resync_remote')) {
            $this->deleteLocalCopy();
        }

        return $this->disk()->delete($this->filename);
    }

    #[Override]
    public function sync(bool $copy = true): TemporaryFile
    {
        if (!$this->localTemporaryFile->exists()) {
            $this->localTemporaryFile = resolve(TemporaryFileFactory::class)
                ->makeLocal(Arr::last(explode('/', $this->filename)));
        }

        if ($copy) {
            $readStream = $this->readStream();

            if (is_resource($readStream)) {
                $this->localTemporaryFile->put($readStream);

                fclose($readStream);
            }
        }

        return $this;
    }

    /**
     * Store on remote disk.
     */
    public function updateRemote(): void
    {
        $this->disk()->copy(
            $this->localTemporaryFile,
            $this->filename
        );
    }

    /**
     * @return resource
     */
    public function readStream()
    {
        return $this->disk()->readStream($this->filename);
    }

    public function contents(): string
    {
        return $this->disk()->get($this->filename);
    }

    /**
     * @param  string|resource  $contents
     */
    public function put($contents): void
    {
        $this->disk()->put($this->filename, $contents);
    }

    public function disk(): Disk
    {
        return $this->diskInstance ??= app(Filesystem::class)->disk($this->disk);
    }
}
