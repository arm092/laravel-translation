<?php

namespace Arm092\Translation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Arm092\Translation\Drivers\Database;
use Arm092\Translation\Drivers\File;

class TranslationManager
{
    private $app;

    private $config;

    private $scanner;

    public function __construct($app, $config, $scanner)
    {
        $this->app = $app;
        $this->config = $config;
        $this->scanner = $scanner;
    }

    public function resolve()
    {
        $driver = $this->config['driver'];
        $driverResolver = Str::studly($driver);
        $method = "resolve{$driverResolver}Driver";

        if (! method_exists($this, $method)) {
            throw new \InvalidArgumentException("Invalid driver [$driver]");
        }

        return $this->{$method}();
    }

    protected function resolveFileDriver()
    {
        return new File(new Filesystem, $this->app['path.lang'], $this->sourceLocale(), $this->scanner);
    }

    protected function resolveDatabaseDriver()
    {
        return new Database($this->sourceLocale(), $this->scanner);
    }

    private function sourceLocale(): string
    {
        $configured = $this->config['source_locale'] ?? null;

        return (string) ($configured ?: $this->app->config['app']['locale']);
    }
}
