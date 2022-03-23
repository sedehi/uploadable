<?php

namespace Sedehi\Uploadable;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ImageMaker
{
    protected static $file;
    protected $maker;
    protected $dimensionMaker;
    protected $path;
    protected $name;
    protected $dimensions;
    protected $originalFilePrefix      = 'original';
    protected $defaultConversionMethod = 'resize';
    protected $disk                    = 'public';
    protected $watermarkPath;
    protected $watermarkPosition;
    protected $largeSizeWidth        = 1920;
    protected $keepOriginal          = false;
    protected $keepLargeSize         = false;
    protected $forceRegenerate       = false;
    protected $includeSubDirectories = false;
    protected $unusedFiles           = [];
    protected $extensions            = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'tif',
        'bmp',
        'ico',
        'psd',
        'webp',
    ];

    public static function make($file = null)
    {
        if (!class_exists(\Intervention\Image\Image::class)) {
            throw new Exception('Intervention package not installed');
        }
        self::$file = $file;

        return new self();
    }

    public function path($path)
    {
        $this->path = rtrim($path, '/') . '/';

        return $this;
    }

    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    public function dimensions(array $dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    public function disk($disk)
    {
        $this->disk = $disk;

        return $this;
    }

    public function watermark($fullPath, $position = 'bottom-right')
    {
        $this->watermarkPath     = $fullPath;
        $this->watermarkPosition = $position;

        return $this;
    }

    public function largeSizeWidth($width)
    {
        $this->largeSizeWidth = $width;

        return $this;
    }

    public function keepOriginal()
    {
        $this->keepOriginal = true;

        return $this;
    }

    public function originalFilePrefix($prefix)
    {
        $this->originalFilePrefix = $prefix;

        return $this;
    }

    public function keepLargeSize()
    {
        $this->keepLargeSize = true;

        return $this;
    }

    public function forceRegenerate()
    {
        $this->forceRegenerate = true;

        return $this;
    }

    public function includeSubDirectories()
    {
        $this->includeSubDirectories = true;

        return $this;
    }

    private function getName($type = null, $dimension = null, $customName = null)
    {
        if (null === $this->name) {
            if (self::$file instanceof UploadedFile) {
                $this->name = time() . '_' . self::$file->hashName();
            } else {
                $this->name = File::basename(self::$file);
            }
        }
        if (null !== $type) {
            if ($type == 'original') {
                return $this->getPrefix($type) . ($customName ?? $this->name);
            }
            if ($type == 'dimension') {
                return $this->getPrefix($type, $dimension) . ($customName ?? $this->name);
            }
        }

        return $this->name;
    }

    private function getPrefix($type, $dimension = null)
    {
        if ($type == 'original') {
            return $this->originalFilePrefix . '-';
        }
        if ($type == 'dimension') {
            $prefix = Arr::get($dimension, 'width', 'auto') . 'x' . Arr::get($dimension, 'height', 'auto') . '-';

            return $prefix . $this->getConversionMethod($dimension) . '-';
        }

        return null;
    }

    private function getConversionMethod($dimension)
    {
        return Arr::get($dimension, 'method', $this->defaultConversionMethod);
    }

    public function store()
    {
        if (!$this->dimensions) {
            throw new Exception('No dimensions specified');
        }
        if ($this->keepOriginal) {
            $this->saveOriginalImage(self::$file);
        }
        if ($this->keepLargeSize) {
            $this->createImage(self::$file, $this->path, $this->getName());
        }
        // create dimenstions
        $this->createImage(self::$file, $this->path, $this->getName(), $this->dimensions);

        return $this->name;
    }

    private function saveOriginalImage($file)
    {
        if ($file instanceof UploadedFile) {
            $file->storeAs($this->path, $this->getName('original'), [
                'disk'       => $this->disk,
                'visibility' => 'private'
            ]);
        } else {
            Storage::disk($this->disk)->put($this->path . $this->getName('original'), file_get_contents($file), [
                'visibility' => 'private'
            ]);
        }
    }

    private function createImage($source, $path = null, $name = null, $dimensions = [])
    {
        $this->maker = Image::make($source)->orientate();
        if (count($dimensions) == 0) {
            $this->createLargeSize($this->maker, $path, $name);

            return;
        }
        $this->createDimensions($dimensions, $path, $name);
    }

    private function createLargeSize($maker, $path = null, $name = null)
    {
        $maker->widen($this->largeSizeWidth, function ($constraint) {
            $constraint->upsize();
        });
        $this->addWatermark($maker);
        Storage::disk($this->disk)->put(($path ?? $this->path) . ($name ?? $this->getName()), $maker->encode(null, 100));
        $maker->destroy();
    }

    private function createDimensions($dimensions, $path = null, $name = null)
    {
        foreach ($dimensions as $dimension) {
            $this->dimensionMaker = clone $this->maker;
            $this->setDimensionConversionProperties($dimension);
            $this->addWatermark($this->dimensionMaker);
            Storage::disk($this->disk)
                   ->put(($path ?? $this->path) . $this->getName('dimension', $dimension, $name), $this->dimensionMaker->encode(null, 100));
            $this->dimensionMaker->destroy();
        }
    }

    private function setDimensionConversionProperties($dimension)
    {
        $method = $this->getConversionMethod($dimension);
        $width  = Arr::get($dimension, 'width');
        $height = Arr::get($dimension, 'height');
        if (null !== $width && null !== $height) {
            return $this->dimensionMaker->{$method}($width, $height);
        }

        return $this->dimensionMaker->{$method}($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
    }

    private function addWatermark($maker)
    {
        if (null !== $this->watermarkPath) {
            $maker->insert($this->watermarkPath, $this->watermarkPosition);
        }
    }

    private function getFullPath($relativePath)
    {
        return Storage::disk($this->disk)->path($relativePath);
    }

    public function remove()
    {
        if (!$this->path || !$this->name) {
            throw new Exception('Path and Name should be specified');
        }
        $files = File::glob($this->getFullPath($this->path . '*' . $this->name));
        if ($this->keepOriginal) {
            $files = array_filter($files, function ($item) {
                return !str_contains($item, $this->originalFilePrefix . '-');
            });
        }
        if ($this->keepLargeSize) {
            $files = array_filter($files, function ($item) {
                return File::basename($item) !== $this->name;
            });
        }
        $files = array_map(function ($item) {
            return $this->path . File::basename($item);
        }, $files);
        Storage::disk($this->disk)->delete($files);
    }

    private function getDimensionFileNames($dimensions, $originalFileName)
    {
        $names = collect();
        foreach ($dimensions as $dimension) {
            $names->put($this->getName('dimension', $dimension, $originalFileName), $dimension);
        }

        return $names;
    }

    private function getFileCollection($path, $fileName = null)
    {
        if (null !== $fileName) {
            $files = glob($this->getFullPath($path . '*' . $fileName), GLOB_NOSORT);
        } else {
            $files = glob($this->getFullPath($path . '*' . '.{' . implode(',', $this->extensions) . '}'), GLOB_BRACE | GLOB_NOSORT);
        }

        // exclude original and large size images and group files with original name
        return collect($files)->filter(function ($file) {
            return preg_match('/^(auto|[0-9]){1,}x(auto|[0-9]){1,}-([a-z]{1,}-)?/i', File::basename($file));
        })->groupBy(function ($item) {
            return preg_replace('/^(auto|[0-9]){1,}x(auto|[0-9]){1,}-([a-z]{1,}-)?/i', '', File::basename($item));
        });
    }

    private function getRegenerateSourceFile($path, $fileName)
    {
        if (null !== self::$file) {
            return self::$file;
        }
        $originalFile = $this->getFullPath($path . $this->getName('original', null, $fileName));
        // if original file exists then regenerate new file from it
        if (File::exists($originalFile)) {
            return $originalFile;
        }
        $largeFile = $this->getFullPath($path . $fileName);
        // if large file exists then regenerate new file from it
        if (File::exists($largeFile)) {
            return $largeFile;
        }
        throw new Exception('No source file exists to regenerate from it.');
    }

    public function regenerate()
    {
        if (!$this->dimensions) {
            throw new Exception('No dimensions specified');
        }
        // regenerate only source file if exists
        if (null !== self::$file) {
            $name = $this->name ?? File::basename(self::$file);
            if (!File::exists(dirname(self::$file) . '/' . $name)) {
                throw new Exception('Source file not found.');
            }
            $this->regenerateFiles($this->path, $name);

            return true;
        }
        $this->regenerateFiles($this->path);
        if ($this->includeSubDirectories) {
            $this->regenerateSubDirectory($this->path);
        }
    }

    private function regenerateFiles($path, $fileName = null)
    {
        foreach ($this->getFileCollection($path, $fileName) as $originalFileName => $collection) {
            $newFileNames = $this->getDimensionFileNames($this->dimensions, $originalFileName);
            // regenerate missing files
            foreach ($newFileNames as $name => $dimension) {
                $exists = $collection->contains(function ($value, $key) use ($name) {
                    return str_contains($value, $name);
                });
                if (!$exists) {
                    $this->createImage($this->getRegenerateSourceFile($path, $originalFileName), $path, $originalFileName, [$dimension]);
                }
            }
            // collect files for delete or force regenerate existing files
            foreach ($collection as $file) {
                $fileName = File::basename($file);
                $exists   = $newFileNames->has($fileName);
                // update unused files array for delete
                if (!$exists) {
                    array_push($this->unusedFiles, $file);
                    continue;
                }
                // if file already exists only remake it if forceRegenerate is set
                if ($exists && $this->forceRegenerate) {
                    $this->createImage($this->getRegenerateSourceFile($path, $originalFileName), $path, $originalFileName, [$newFileNames->get($fileName)]);
                }
            }
        }
        // delete unused files
        if (count($this->unusedFiles)) {
            $this->unusedFiles = array_map(function ($item) use ($path) {
                return $path . '/' . File::basename($item);
            }, $this->unusedFiles);
            Storage::disk($this->disk)->delete($this->unusedFiles);
        }
    }

    private function regenerateSubDirectory($path)
    {
        $diskPath = $this->getFullPath(null);
        $pattern  = $diskPath . $path . '*';
        $folders  = glob($pattern, GLOB_ONLYDIR);
        if (count($folders)) {
            foreach ($folders as $folder) {
                $this->regenerateSubDirectory(substr($folder, strlen($diskPath)) . '/');
            }
        }
        $this->regenerateFiles($path);
    }
}
