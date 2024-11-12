<?php

namespace TinCat\HdrjpgSdkPhp\Entity;

class ConversionFile
{
    const STATUS_PENDING = 0;
    const STATUS_PREPARED = 1;
    const STATUS_RUNNING = 2;
    const STATUS_FAILED = 3;
    const STATUS_SKIPPED = 4;
    const STATUS_COMPLETED = 5;

    public string $uuid;
    public int $status;
    public int $createdDate;
    public int $failedDate;
    public array $failDescriptions;
    public array $warningDescriptions;
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
