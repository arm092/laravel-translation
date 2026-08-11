<?php

namespace Arm092\Translation\Support;

use Illuminate\Filesystem\Filesystem;

final class AtomicFileWriter
{
    public function __construct(private Filesystem $files) {}

    public function write(string $path, string $contents): void
    {
        $this->files->ensureDirectoryExists(dirname($path));
        $backup = $path.'.bak';
        $existed = $this->files->exists($path);
        if ($existed) {
            $this->files->copy($path, $backup);
        }
        try {
            $this->files->replace($path, $contents);
            if ($this->files->exists($backup)) {
                $this->files->delete($backup);
            }
        } catch (\Throwable $exception) {
            if ($existed && $this->files->exists($backup)) {
                $this->files->copy($backup, $path);
            } elseif (! $existed && $this->files->exists($path)) {
                $this->files->delete($path);
            }
            throw $exception;
        }
    }
}
