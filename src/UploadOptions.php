<?php

namespace Sedehi\Artist\Libs;

class UploadOptions
{
    public $disk = 'public';
    public $dimensions;
    public $path;
    public $field;
    public $name;
    public $keepLargeSize = false;
    public $keepOriginal  = false;

    public static function make()
    {
        return new self();
    }

    public function dimensions(array $dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    public function field($field)
    {
        $this->field = $field;

        return $this;
    }

    public function disk($disk)
    {
        $this->disk = $disk;

        return $this;
    }

    public function keepOriginal()
    {
        $this->keepOriginal = true;

        return $this;
    }

    public function keepLargeSize()
    {
        $this->keepLargeSize = true;

        return $this;
    }

    public function path($path)
    {
        $this->path = rtrim($path, '/') . '/';

        return $this;
    }
}
