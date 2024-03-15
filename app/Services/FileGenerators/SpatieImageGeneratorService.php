<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Services\FileGenerators;

use App;
use App\Exceptions\Custom\FileGeneratorException;
use App\Interfaces\FileGenerators\ImageGeneratorServiceInterface;
use Spatie\Browsershot\Browsershot;

/**
 * Spatie Image Generator Service
 *
 * @package \App\Services\FileGenerators
 */
class SpatieImageGeneratorService extends BaseFileGeneratorService implements ImageGeneratorServiceInterface
{
    /**
     * @var array
     */
    protected array $options;

    /**
     * SpatieImageGeneratorService constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->options = config('browsershot.image');
    }

    /**
     * @param string $fileName
     * @param mixed  $html
     * @param array  $options
     * @param bool   $returnAsBase64
     * @return string|bool
     */
    public function createFromHtml(string $fileName, mixed $html, array $options = [], bool $returnAsBase64 = false) : string|bool
    {
        try {
            $basePath = !empty($options['base_path']) ? $options['base_path'] : config('filesystems.disks.local_tmp.root');

            $image = Browsershot::html($html)
                ->fullPage();

            /*
             * Prepare options
             */
            foreach (array_merge($this->options, $options) as $option => $value) {
                if ($option === 'timeout') {
                    $image->timeout($value);
                } else if ($option === 'type') {
                    $image->setScreenshotType($value);
                } else {
                    $image->setOption($option, $value);
                }
            }

            /*
             * Generate file
             */
            if ($returnAsBase64) {
                return $image->base64Screenshot();
            }

            $path = $basePath . $this->ds . uniqid($fileName . '_') . '.png';

            $image->save($path);

            return $path;
        } catch (\Exception $exception) {
            if (!App::environment('production')) {
                throw new FileGeneratorException($exception->getMessage(), $exception->getCode(), $exception);
            }

            return false;
        }
    }

    /**
     * @param string $templateName
     * @param mixed  $data
     * @param array  $options
     * @param bool   $returnAsBase64
     * @return mixed
     */
    public function createFromBlade(string $templateName, mixed $data, array $options = [], bool $returnAsBase64 = false) : mixed
    {
        $html = $this->getHtmlFromBlade($templateName, $data);

        if (!empty($options['file_name'])) {
            $templateName = $options['file_name'];

            unset($options['file_name']);
        }

        return $this->createFromHtml($templateName, $html, $options, $returnAsBase64);
    }
}
