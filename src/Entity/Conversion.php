<?php

namespace TinCat\HdrjpgSdkPhp\Entity;

class Conversion
{
    const STATUS_PENDING = 0;
    const STATUS_RUNNING = 1;
    const STATUS_FAILED = 2;
    const STATUS_READY = 3;
    const STATUS_DELIVERED = 4;

    const STEP_PRIMING = 0;
    const STEP_PREPARING = 1;
    const STEP_CONVERTING = 2;
    const STEP_FINALIZING = 3;

    public string $uuid;
    public int $status;
    public ?int $step;
    public int $createdDate;
    public int $failedDate;
    public array $failDescriptions;
    public array $developerFailDescriptions;
    public int $completedDate;
    public int $deliveredDate;
    public int $deliveryExpirationDate;
    public string $sourceImageFileName;
    public float $progressPercentage;
    public int $conversionTime;
    public int $estimatedRemainingTime;
    public ?string $blurHash;
    public ?string $averageColor;
    public array $variants;

    public function isCanBeDownloaded(): bool
    {
        return $this->status === self::STATUS_READY || $this->status === self::STATUS_DELIVERED;
    }

    public function isDifferentState(Conversion $conversion): bool
    {
        return
            $conversion->status !== $this->status
            ||
            $conversion->step !== $this->step
            ||
            $conversion->estimatedRemainingTime !== $this->estimatedRemainingTime
            ||
            $conversion->progressPercentage !== $this->progressPercentage;
    }
}
