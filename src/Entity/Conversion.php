<?php

namespace TinCat\HdrjpgSdkPhp\Entity;

class Conversion
{
    const STATUS_PENDING = 0;
    const STATUS_RUNNING = 1;
    const STATUS_FAILED = 2;
    const STATUS_READY = 3;
    const STATUS_DELIVERED = 4;

    public string $uuid;
    public int $status;
    public int $createdDate;
    public int $failedDate;
    public int $completedDate;
    public int $deliveredDate;
    public int $deliveryExpirationDate;
    public string $sourceImageFileName;
    public float $progressPercentage;
    public int $conversionTime;
    public int $estimatedRemainingTime;
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
            $conversion->progressPercentage !== $this->progressPercentage;
    }
}
