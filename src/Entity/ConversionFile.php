<?php

namespace TinCat\HdrjpgSdkPhp\Entity;

class ConversionFile
{
    const STATUS_PENDING = 0;
    const STATUS_RUNNING = 1;
    const STATUS_FAILED = 2;
    const STATUS_COMPLETED = 3;

    const OUTPUT_FORMAT_JPEG = 0;
    const OUTPUT_FORMAT_JXL = 1;
    const OUTPUT_FORMAT_AVIF = 2;

    public string $uuid;
    public int $status;
    public int $createdDate;
    public int $failedDate;
    public array $failDescriptions;
    public int $completedDate;
    public float $conversionTime;
    public int $outputFormat;
    public string $outputImageFileName;
    public ConversionParameters $conversionParameters;
}
