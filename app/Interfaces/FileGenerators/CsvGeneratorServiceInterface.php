<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Interfaces\FileGenerators;

/**
 * Csv Generator Service Interface
 *
 * @package \App\Interfaces\FileGenerators
 */
interface CsvGeneratorServiceInterface
{
    /**
     * @param string $fileName
     * @param mixed  $data
     * @param bool   $returnAsBase64
     * @return string|bool
     */
    public function create(string $fileName, mixed $data, bool $returnAsBase64) : string|bool;
}
