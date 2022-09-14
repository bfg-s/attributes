<?php

namespace Bfg\Attributes\Scanner;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

/**
 * Class ScanDirectories
 * @package Bfg\Object\ClassStorageCore
 */
class ScanDirectories
{
    /**
     * @var Collection
     */
    public Collection $directories;

    /**
     * @var array|string[]
     */
    static array $ignore_folder_names = [
        'node_modules',
        'css',
        'js',
    ];

    /**
     * @var array
     */
    static array $ignore_folders = [];

    /**
     * @var array
     */
    static array $extend_folders = [];

    /**
     * ScanDirectories constructor.
     * @param  Filesystem  $filesystem
     * @param  string|null  $path
     */
    public function __construct(
        protected Filesystem $filesystem,
        string $path = null
    ) {
        if (!$path) {

            static::$ignore_folders[] = base_path('bfg');
            static::$ignore_folders[] = base_path('public');
            static::$ignore_folders[] = base_path('resources');
            static::$ignore_folders[] = base_path('storage');
            static::$ignore_folders[] = base_path('runtimes');
            static::$ignore_folders[] = base_path('database');
            static::$extend_folders[] = base_path('vendor/bfg');
        }

        static::$ignore_folders[] = base_path('vendor');

        $this->directories = $this
            ->makeList($path ?: base_path())
            ->merge(collect(static::$extend_folders)->map(
                fn ($folder) => $this->makeList($folder)
            )->collapse())->prepend($path);
    }

    /**
     * @param  string  $dir
     * @return Collection
     */
    protected function makeList(string $dir): Collection
    {
        $dirs = collect($this->filesystem->directories($dir))
            ->map(fn ($i) => str_replace($dir, '', $i))
            ->map(fn ($i) => trim(trim($i), '/'))
            ->filter(fn($i) => !in_array($i, static::$ignore_folder_names))
            ->map(fn ($i) => rtrim($dir, '/') . '/' . $i)
            ->filter(fn($i) => !in_array($i, static::$ignore_folders));

        foreach ($dirs as $dir) {

            $dirs = $dirs->merge($this->makeList($dir));
        }

        return $dirs;
    }
}
