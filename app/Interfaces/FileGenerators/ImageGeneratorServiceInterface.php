<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Interfaces\FileGenerators;

/**
 * Image Generator Service Interface
 *
 * @package \App\Interfaces\FileGenerators
 */
interface ImageGeneratorServiceInterface
{
    /**
     * @param string $fileName
     * @param mixed  $html
     * @param array  $options
     * @param bool   $returnAsBase64
     * @return string|bool
     */
    public function createFromHtml(string $fileName, mixed $html, array $options, bool $returnAsBase64) : string|bool;

    /**
     * @param string $templateName
     * @param mixed  $data
     * @param array  $options
     * @param bool   $returnAsBase64
     * @return mixed
     */
    public function createFromBlade(string $templateName, mixed $data, array $options, bool $returnAsBase64) : mixed;
}
