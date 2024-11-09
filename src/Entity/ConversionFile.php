<?php

namespace TinCat\HdrjpgSdkPhp\Entity;

class ConversionFile
{
    const STATUS_PENDING = 0;
    const STATUS_RUNNING = 1;
    const STATUS_FAILED = 2;
    const STATUS_SKIPPED = 3;
    const STATUS_COMPLETED = 4;

    public string $uuid;
    public int $status;
    public int $createdDate;
    public int $failedDate;
    public array $failDescriptions;
    public array $developerFailDescriptions;
    public int $completedDate;
    public float $conversionTime;
    public string $fileName;
    public string $format;
    public string $outputImageFileName;
    public int $outputImageFileSize;
    public int $outputImageWidth;
    public int $outputImageHeight;
    public float $outputImageMegapixels;
    public float $outputImageAspectRatio;
    public float $outputImageCompressionRatio;
    public ConversionParameters $conversionParameters;
}
