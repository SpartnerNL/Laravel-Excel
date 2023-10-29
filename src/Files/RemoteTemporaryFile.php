<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Support\Arr;

class RemoteTemporaryFile extends TemporaryFile
{
    /**
     * @var string
     */
    private $disk;

    /**
     * @var Disk|null
     */
    private $diskInstance;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var LocalTemporaryFile
     */
    private $localTemporaryFile;

    /**
     * @param  string  $disk
     * @param  string  $filename
     * @param  LocalTemporaryFile  $localTemporaryFile
     */
    public function __construct(string $disk, string $filename, LocalTemporaryFile $localTemporaryFile)
    {
        $this->disk               = $disk;
        $this->filename           = $filename;
        $this->localTemporaryFile = $localTemporaryFile;

        $this->disk()->touch($filename);
    }

    public function __sleep()
    {
        return ['disk', 'filename', 'localTemporaryFile'];
    }

    /**
     * @return string
     */
    public function getLocalPath(): string
    {
        return $this->localTemporaryFile->getLocalPath();
    }

    /**
     * @return bool
     */
    public function existsLocally(): bool
    {
        return $this->localTemporaryFile->exists();
    }

    /**
     * @return bool
     */
    public function exists(): bool
    {
        return $this->disk()->exists($this->filename);
    }

    /**
     * @return bool
     */
    public function deleteLocalCopy(): bool
    {
        return $this->localTemporaryFile->delete();
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        // we don't need to delete local copy as it's deleted at end of each chunk
        if (!config('excel.temporary_files.force_resync_remote')) {
            $this->deleteLocalCopy();
        }

        return $this->disk()->delete($this->filename);
    }

    /**
     * @return TemporaryFile
     */
    public function sync(): TemporaryFile
    {
        if (!$this->localTemporaryFile->exists()) {
            $this->localTemporaryFile = resolve(TemporaryFileFactory::class)
                ->makeLocal(Arr::last(explode('/', $this->filename)));
        }

        $this->disk()->copy(
            $this,
            $this->localTemporaryFile->getLocalPath()
        );

        return $this;
    }

    /**
     * Store on remote disk.
     */
    public function updateRemote()
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

    /**
     * @return string
     */
    public function contents(): string
    {
        return $this->disk()->get($this->filename);
    }

    /**
     * @param  string|resource  $contents
     */
    public function put($contents)
    {
        $this->disk()->put($this->filename, $contents);
    }

    /**
     * @return Disk
     */
    public function disk(): Disk
    {
        return $this->diskInstance ?: $this->diskInstance = app(Filesystem::class)->disk($this->disk);
    }
}
